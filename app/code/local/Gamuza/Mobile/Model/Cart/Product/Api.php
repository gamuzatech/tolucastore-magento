<?php
/**
 * @package     Gamuza_Mobile
 * @copyright   Copyright (c) 2017 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

/**
 * See the AUTHORS file for a list of people on the Gamuza Team.
 * See the ChangeLog files for a list of changes.
 * These files are distributed with gamuza_mobile-magento at http://github.com/gamuzatech/.
 */

class Gamuza_Mobile_Model_Cart_Product_Api extends Gamuza_Mobile_Model_Api_Resource_Product
{
    const PRICE_TYPE_FIXED = 'fixed';

    const DISTRO_STORE_ID = Mage_Core_Model_App::DISTRO_STORE_ID;

    /**
     * Base preparation of product data
     *
     * @param  mixed $data
     * @return null|array
     */
    protected function _prepareProductsData ($data)
    {
        return is_array ($data) ? $data : null;
    }

    /**
     * @param  $quoteId
     * @param  $productsData
     * @param  $store
     * @return bool
     */
    public function add($store = null, $productsData = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store, true);

        if (empty ($productsData))
        {
            $this->_fault ('product_data_not_specified');
        }

        $productsData = $this->_prepareProductsData($productsData);

        if (empty($productsData))
        {
            $this->_fault('invalid_product_data');
        }

        $errors = array();

        foreach ($productsData as $productItem)
        {
            if (isset($productItem['product_id']))
            {
                $productByItem = $this->_getProduct($productItem['product_id'], self::DISTRO_STORE_ID, "id");
            }
            else if (isset($productItem['sku']))
            {
                $productByItem = $this->_getProduct($productItem['sku'], self::DISTRO_STORE_ID, "sku");
            }
            else
            {
                $errors [] = Mage::helper('checkout')->__("One item of products do not have identifier or sku");

                continue;
            }

            $productRequest = $this->_getProductRequest($productItem);

            /**
             * Options MaxLength
             */
            $options = $productRequest->getData ('options');

            if (is_array ($options) && count ($options) > 0)
            {
                foreach ($options as $id => $value)
                {
                    $valueCount = substr_count ($value, ',') + 1;

                    $productOptions = $productByItem->getOptions ();

                    if ($valueCount > 1 && count ($productOptions) > 0)
                    {
                        foreach ($productOptions as $option)
                        {
                            if ($option->getOptionId () == $id && $option->getIsRequire ())
                            {
                                $optionMaxLength = intval ($option->getMaxLength ());

                                if ($optionMaxLength > 0 && $valueCount > $optionMaxLength)
                                {
                                    $errors [] = Mage::helper ('checkout')->__("You can select up to %s options in '%s'", $option->getMaxLength (), $option->getTitle ());

                                    continue;
                                }
                            }
                        }
                    }
                }
            }

            /**
             * Bundle
             */
            $bundleOption = $productRequest->getData ('bundle_option');

            if (!empty ($bundleOption))
            {
                foreach ($bundleOption as $id => $value)
                {
                    $bundleOption [$id] = explode (',', $value);
                }

                $productRequest->setData ('bundle_option', $bundleOption);
            }

            try
            {
                $result = $quote->addProduct($productByItem, $productRequest);

                if (is_string($result))
                {
                    Mage::throwException($result);
                }
            }
            catch (Mage_Core_Exception $e)
            {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors))
        {
            $this->_fault("add_product_fault", implode(PHP_EOL, $errors));
        }

        try
        {
            $quote->collectTotals()->save();
        }
        catch (Exception $e)
        {
            $this->_fault("add_product_quote_save_fault", $e->getMessage());
        }

        return true;
    }

    /**
     * @param  $quoteId
     * @param  $productsData
     * @param  $store
     * @return bool
     */
    public function update($store = null, $productsData = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        if (empty ($productsData))
        {
            $this->_fault ('product_data_not_specified');
        }

        $productsData = $this->_prepareProductsData($productsData);

        if (empty($productsData))
        {
            $this->_fault('invalid_product_data');
        }

        $errors = array();

        foreach ($productsData as $productItem)
        {
            if (isset($productItem['product_id']))
            {
                $productByItem = $this->_getProduct($productItem['product_id'], self::DISTRO_STORE_ID, "id");
            }
            else if (isset($productItem['sku']))
            {
                $productByItem = $this->_getProduct($productItem['sku'], self::DISTRO_STORE_ID, "sku");
            }
            else
            {
                $errors[] = Mage::helper('checkout')->__("One item of products do not have identifier or sku");

                continue;
            }

            try {

            /** @var $quoteItem Mage_Sales_Model_Quote_Item */
            $quoteItem = $this->_getQuoteItemByProduct(
                $quote, $productByItem, $this->_getProductRequest($productItem)
            );

            } catch (Exception $e) {

                $this->_fault ('update_product_fault', $e->getMessage ());

            }

            if (!$quoteItem || !$quoteItem->getId())
            {
                $errors[] = Mage::helper('checkout')->__("One item of products is not belong any of quote item");

                continue;
            }
/*
            if ($productItem['qty'] > 0)
            {
                $quoteItem->setQty($productItem['qty']);
            }
*/
            try
            {
                $quoteItem->setData ('qty', 0); // force UPDATE instead SUM

                $result = $quote->updateItem ($quoteItem->getId (), $this->_getProductRequest ($productItem));

                if (is_string($result))
                {
                    Mage::throwException($result);
                }
            }
            catch (Mage_Core_Exception $e)
            {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors))
        {
            $this->_fault("update_product_fault", implode(PHP_EOL, $errors));
        }

        try
        {
            $quote->collectTotals()->save();
        }
        catch (Exception $e)
        {
            $this->_fault("update_product_quote_save_fault", $e->getMessage());
        }

        return true;
    }

    /**
     * @param  $quoteId
     * @param  $productsData
     * @param  $store
     * @return bool
     */
    public function remove($store = null, $productsData = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        if (empty ($productsData))
        {
            $this->_fault ('product_data_not_specified');
        }

        $productsData = $this->_prepareProductsData($productsData);

        if (empty($productsData))
        {
            $this->_fault('invalid_product_data');
        }

        $errors = array();

        foreach ($productsData as $productItem)
        {
            if (isset($productItem['product_id']))
            {
                $productByItem = $this->_getProduct($productItem['product_id'], self::DISTRO_STORE_ID, "id");
            }
            else if (isset($productItem['sku']))
            {
                $productByItem = $this->_getProduct($productItem['sku'], self::DISTRO_STORE_ID, "sku");
            }
            else
            {
                $errors[] = Mage::helper('checkout')->__("One item of products do not have identifier or sku");

                continue;
            }

            try
            {
                /** @var $quoteItem Mage_Sales_Model_Quote_Item */
                $quoteItem = $this->_getQuoteItemByProduct(
                    $quote, $productByItem, $this->_getProductRequest($productItem)
                );

                if (!$quoteItem || !$quoteItem->getId())
                {
                    $errors[] = Mage::helper('checkout')->__("One item of products is not belong any of quote item");

                    continue;
                }

                $quote->removeItem($quoteItem->getId());
            }
            catch (Mage_Core_Exception $e)
            {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors))
        {
            $this->_fault("remove_product_fault", implode(PHP_EOL, $errors));
        }

        try
        {
            $quote->collectTotals()->save();
        }
        catch (Exception $e)
        {
            $this->_fault("remove_product_quote_save_fault", $e->getMessage());
        }

        return true;
    }

    /**
     * @param  $quoteId
     * @param  $store
     * @return array
     */
    public function items($store = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        if (!$quote->getItemsCount())
        {
            return array();
        }

        $productsResult = array();

        $minimumAmount      = null;
        $minimumCurrency    = null;
        $minimumDescription = null;
        $minimumMessage     = null;

        if (!$quote->validateMinimumAmount ())
        {
            $minimumAmount      = Mage::getStoreConfig ('sales/minimum_order/amount');
            $minimumCurrency    = Mage::helper('core')->currency ($minimumAmount, true, false);

            $minimumDescription = Mage::getStoreConfig ('sales/minimum_order/description');
            $minimumMessage     = $minimumDescription ? $minimumDescription
                : Mage::helper ('checkout')->__('Minimum order amount is %s', $minimumCurrency)
            ;
        }

        $mediaUrl = Mage::app ()
            ->getStore (Mage_Core_Model_App::ADMIN_STORE_ID)
            ->getBaseUrl (Mage_Core_Model_Store::URL_TYPE_MEDIA, false)
        ;

        foreach ($quote->getAllVisibleItems () as $item)
        {
            /*
            if (!empty ($minimumMessage))
            {
                $item->setHasError (true)
                    ->setMessage ($minimumMessage)
                ;
            }
            */

            /** @var $item Mage_Sales_Model_Quote_Item */
            $product = $item->getProduct();
            $stockItem = $product->getStockItem();

            $qtyIncrements = $stockItem->getQtyIncrements();

            $itemOptions = $item->getBuyRequest()->getData('options');

            $itemAdditionalOptions = $item->getBuyRequest()->getData('additional_options');

            $itemSuperAttribute = $item->getBuyRequest()->getData('super_attribute');

            $itemBundleOption = $item->getBuyRequest()->getData('bundle_option');

            $productData = array(
                'product_id'              => intval($item->getProductId()),
                // Basic item data
                'name'                    => $item->getName(),
                'free_shipping'           => boolval($item->getFreeShipping()),
                'is_qty_decimal'          => boolval($item->getIsQtyDecimal()),
                'no_discount'             => boolval($item->getNoDiscount()),
                'weight'                  => floatval($item->getWeight()),
                'qty'                     => $item->getQty(),
                'price'                   => floatval($item->getPrice()),
                'base_price'              => floatval($item->getBasePrice()),
                'discount_percent'        => floatval($item->getDiscountPercent()),
                'discount_amount'         => floatval($item->getDiscountAmount()),
                'base_discount_amount'    => floatval($item->getBaseDiscountAmount()),
                'row_total'               => floatval($item->getRowTotal()),
                'base_row_total'          => floatval($item->getBaseRowTotal()),
                'row_total_with_discount' => floatval($item->getRowTotalWithDiscount()),
                'row_weight'              => floatval($item->getRowWeight()),
                // Basic product data
                'url_path'                => $product->getUrlPath(),
                'thumbnail'               => $mediaUrl . 'catalog/product' . $product->getData ('thumbnail'),
                'small_image'             => $mediaUrl . 'catalog/product' . $product->getData ('small_image'),
                'image'                   => $mediaUrl . 'catalog/product' . $product->getData ('image'),
                'special_from_date'       => $product->getSpecialFromDate(),
                'special_to_date'         => $product->getSpecialToDate(),
                'special_price'           => $product->getSpecialPrice(),
                'has_error'               => boolval ($item->getHasError ()),
                'message'                 => $item->getMessage (),
                // Basic stock data
                'stock_qty'               => intval($stockItem->getQty()),
                'min_qty'                 => $stockItem->getMinQty(),
                'backorders'              => $stockItem->getBackorders(),
                'min_sale_qty'            => $stockItem->getMinSaleQty(),
                'max_sale_qty'            => $stockItem->getMaxSaleQty(),
                'is_in_stock'             => boolval($stockItem->getIsInStock()),
                'qty_increments'          => intval($qtyIncrements) > 1 ? $qtyIncrements : 1,
                'enable_qty_increments'   => $stockItem->getEnableQtyIncrements(),
                'is_decimal_divided'      => boolval($stockItem->getIsDecimalDivided()),
                'ordered_items'           => intval($stockItem->getOrderedItems()),
                // custom options
                'options'                 => $itemOptions,
                // additional_options
                'additional_options'      => $itemAdditionalOptions,
                // super_attribute
                'super_attribute'         => $itemSuperAttribute,
                // bundle_option
                'bundle_option'           => $itemBundleOption,
                // minimum
                'minimum_amount'      => floatval ($minimumAmount),
                'minimum_currency'    => $minimumCurrency,
                'minimum_description' => $minimumDescription,
                'minimum_message'     => $minimumMessage,
            );

            $productData ['product_options'] = array ();

            foreach ($itemOptions as $itemOptionId => $itemOptionValues)
            {
                $itemOptionValues = explode (',', $itemOptionValues);

                foreach ($item->getProduct ()->getOptions () as $option)
                {
                    if ($option->getOptionId () == $itemOptionId)
                    {
                        $resultOption = array(
                            'option_id'     => intval ($option->getId ()),
                            'product_id'    => intval ($option->getProductId ()),
                            'type'          => $option->getType (),
                            'is_require'    => boolval ($option->getIsRequire ()),
                            'sort_order'    => intval ($option->getSortOrder ()),
                            'max_length'    => intval ($option->getMaxLength ()),
                            'default_title' => $option->getDefaultTitle (),
                            'title'         => $option->getTitle (),
                        );

                        foreach ($option->getValues() as $value)
                        {
                            if (in_array ($value->getOptionTypeId (), $itemOptionValues))
                            {
                                $resultOption ['values'][] = array(
                                    'option_type_id'     => intval ($value->getOptionTypeId ()),
                                    'option_id'          => intval ($value->getOptionId ()),
                                    'sort_order'         => intval ($value->getSortOrder ()),
                                    'default_price'      => floatval ($value->getDefaultPrice ()),
                                    'default_price_type' => $value->getDefaultPriceType (),
                                    'store_price'        => floatval ($value->getStorePrice ()),
                                    'store_price_type'   => $value->getStorePriceType (),
                                    'price'              => floatval ($value->getPrice ()),
                                    'price_type'         => $value->getPriceType (),
                                    'default_title'      => $value->getDefaultTitle (),
                                    'store_title'        => $value->getStoreTitle (),
                                    'title'              => $value->getTitle (),
                                );
                            }
                        }

                        $productData ['product_options'][] = $resultOption;
                    }
                }
            }

            $productData ['product_bundle_option'] = array ();

            if (!strcmp ($product->getTypeId (), Mage_Catalog_Model_Product_Type::TYPE_BUNDLE))
            {
                foreach ($itemBundleOption as $itemBundleOptionId => $itemBundleOptionValues)
                {
                    // $itemBundleOptionValues = explode (',', $itemBundleOptionValues);

                    $optionsCollection    = $product->getTypeInstance (true)->getOptionsCollection ($product);
                    $selectionsCollection = $product->getTypeInstance (true)->getSelectionsCollection ($optionsCollection->getAllIds (), $product);

                    foreach ($optionsCollection->appendSelections ($selectionsCollection) as $option)
                    {
                        if ($option->getId () == $itemBundleOptionId)
                        {
                            $resultOption = array(
                                'option_id'     => intval ($option->getId ()),
                                'product_id'    => intval ($option->getParentId ()),
                                'type'          => $option->getType (),
                                'is_require'    => boolval ($option->getRequired ()),
                                'sort_order'    => intval ($option->getPosition ()),
                                'default_title' => $option->getDefaultTitle (),
                                'title'         => $option->getTitle (),
                            );

                            foreach ($option->getSelections() as $selection)
                            {
                                if (in_array ($selection->getSelectionId (), $itemBundleOptionValues))
                                {
                                    $resultOption ['selections'][] = array(
                                        'option_type_id'     => intval ($selection->getSelectionId ()),
                                        'option_id'          => intval ($selection->getOptionId ()),
                                        'sort_order'         => intval ($selection->getPosition ()),
                                        'default_price'      => floatval ($selection->getDefaultPrice ()),
                                        'default_price_type' => $selection->getDefaultPriceType (),
                                        'store_price'        => floatval ($selection->getStorePrice ()),
                                        'store_price_type'   => $selection->getStorePriceType (),
                                        'price'              => floatval ($selection->getPrice ()),
                                        'price_type'         => self::PRICE_TYPE_FIXED,
                                        'special_price'      => floatval ($selection->getSpecialPrice ()),
                                        'default_title'      => $selection->getDefaultTitle (),
                                        'store_title'        => $selection->getStoreTitle (),
                                        'title'              => $selection->getName (),
                                        // bundle
                                        'selection_qty'            => floatval ($selection->getSelectionQty ()),
                                        'selection_can_change_qty' => boolval ($selection->getSelectionCanChangeQty ()),
                                        'is_default'               => boolval ($selection->getIsDefault ()),
                                    );
                                }
                            }

                            $productData ['product_bundle_option'][] = $resultOption;
                        }
                    }
                }
            }

            $productData ['product_super_attribute'] = array ();

            if (!strcmp ($product->getTypeId (), Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE))
            {
                foreach ($itemSuperAttribute as $itemSuperAttributeId => $itemSuperAttributeValues)
                {
                    $itemSuperAttributeValues = explode (',', $itemSuperAttributeValues);

                    $configurableAttributesArray = $product->getTypeInstance (true)->getConfigurableAttributesAsArray ($product);

                    foreach ($configurableAttributesArray as $configurableAttributesItem)
                    {
                        if ($configurableAttributesItem ['attribute_id'] == $itemSuperAttributeId)
                        {
                            $configurableAttributesItem ['id']           = intval ($configurableAttributesItem ['id']);
                            $configurableAttributesItem ['use_default']  = boolval ($configurableAttributesItem ['use_default']);
                            $configurableAttributesItem ['position']     = intval ($configurableAttributesItem ['position']);
                            $configurableAttributesItem ['attribute_id'] = intval ($configurableAttributesItem ['attribute_id']);

                            $configurableAttributesCopy = $configurableAttributesItem;

                            $configurableAttributesCopy ['values'] = array (); // unset

                            foreach ($configurableAttributesItem ['values'] as $value)
                            {
                                if (in_array ($value ['value_index'], $itemSuperAttributeValues))
                                {
                                    $value ['product_super_attribute_id'] = intval ($value ['product_super_attribute_id']);
                                    $value ['value_index']                = intval ($value ['value_index']);
                                    $value ['is_percent']                 = boolval ($value ['is_percent']);
                                    $value ['pricing_value']              = floatval ($value ['pricing_value']);
                                    $value ['use_default_value']          = boolval ($value ['use_default_value']);
                                    $value ['order']                      = intval ($value ['order']);

                                    $configurableAttributesCopy ['values'][] = $value;
                                }
                            }

                            $productData ['product_super_attribute'][] = $configurableAttributesCopy;
                        }
                    }
                }
            }

            $productsResult [] = $productData;
        }

        return $productsResult;
    }
}

