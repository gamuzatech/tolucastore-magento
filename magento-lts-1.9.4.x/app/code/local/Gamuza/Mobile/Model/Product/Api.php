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

class Gamuza_Mobile_Model_Product_Api extends Mage_Catalog_Model_Api_Resource
{
    const PRICE_TYPE_FIXED = 'fixed';

    protected $_attributeCodes = array (
        'brand',
        'brand_value',
        'color',
        'color_value',
        'description',
        'free_shipping',
        'gift_message_available',
        'has_options',
        'image',
        'image_label',
        'name',
        'news_from_date',
        'news_to_date',
        'price',
        'price_type',
        'price_view',
        'required_options',
        'short_description',
        'size',
        'size_value',
        'sku',
        'sku_position',
        'small_image',
        'small_image_label',
        'special_from_date',
        'special_price',
        'special_to_date',
        'status',
        'thumbnail',
        'thumbnail_label',
        'type_id',
        'url_key',
        'url_path',
        'visibility',
        'volume_altura',
        'volume_comprimento',
        'volume_largura',
        'weight',
    );

    protected $_descCodes  = array ('description', 'short_description');
    protected $_imageCodes = array ('image', 'small_image', 'thumbnail');
    protected $_floatCodes = array ('price');
    protected $_intCodes   = array ('sku_position', 'status', 'visibility', 'weight');
    protected $_boolCodes  = array ('free_shipping', 'gift_message_available', 'has_options', 'required_options');

    protected $_filtersMap = array(
        'product_id' => 'entity_id',
        'type'       => 'type_id',
        'set'        => 'attribute_set_id',
    );

    public function __construct ()
    {
        // parent::__construct ();

        $this->_entityTypeId = Mage::getModel ('eav/entity')
            ->setType (Mage_Catalog_Model_Product::ENTITY)
            ->getTypeId ()
        ;

        /*
        $this->_freeshippingAttribute = Mage::getModel ('eav/entity_attribute')
            ->loadByCode ($this->_entityTypeId, 'free_shipping')
        ;
        */
    }

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @param null|object|array $filters
     * @return array
     */
    public function items ($filters = null, $store = null, $category = null)
    {
        $storeId = Mage_Core_Model_App::DISTRO_STORE_ID;

        $storeCategoryId  = Mage::app ()->getStore ($storeId)->getRootCategoryId ();
        $baseCategoryPath = Mage_Catalog_Model_Category::TREE_ROOT_ID . '/' . $storeCategoryId;

        $status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;

        $typeIds = array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        );

        $visibility = array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
        );

        Mage::app ()->getStore ()->setConfig (
            Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT, '1'
        );

        $collection = Mage::getResourceModel ('mobile/catalog_product_collection'); // ->getCollection ();

        $collection->getSelect ()->reset (Zend_Db_Select::FROM)
            ->from (array ('e' => Mage::getSingleton ('core/resource')->getTableName ('catalog_product_flat_' . $storeId)))
            ->order ('e.name')
        ;

        $collection /* after from */
            ->addStoreFilter ($storeId)
            ->setFlag ('require_stock_items', true)
            ->addAttributeToFilter ('status', array ('neq' => $status))
            ->addAttributeToFilter ('type_id', array ('in' => $typeIds))
            ->addAttributeToFilter ('visibility', array ('in' => $visibility))
            ->addAttributeToSelect ($this->_attributeCodes)
            /* compare to category_product_index table (filter inactive stores) */
            ->joinField(
                'category_id', 'catalog_category_product_index',
                'category_id', 'product_id = entity_id',
                "at_category_id.store_id = {$storeId}", 'inner'
            )
        ;

        $collection->getSelect ()->group ('e.entity_id')
            ->join(
                array ('ccfs' => 'catalog_category_flat_store_' . $storeId),
                'ccfs.entity_id = at_category_id.category_id',
                array ('category_name' => "ccfs.name")
            )
            ->where ("ccfs.path LIKE '{$baseCategoryPath}/%'")
            ->order ('ccfs.path')
        ;

        $collection->getSelect ()->reset (Zend_Db_Select::ORDER)
            ->order ('ccfs.position')
            ->order ('e.name')
        ;

        /** @var $apiHelper Mage_Api_Helper_Data */
        $apiHelper = Mage::helper ('api');

        $filters = $apiHelper->parseFilters ($filters, $this->_filtersMap);

        try
        {
            foreach ($filters as $field => $value)
            {
                $collection->addFieldToFilter ($field, $value);
            }
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_fault ('filters_invalid', $e->getMessage ());
        }

        $collection->addOptionsToResult ();

        $result = array ();

        $mediaUrl = Mage::app ()
            ->getStore (Mage_Core_Model_App::DISTRO_STORE_ID)
            ->getBaseUrl (Mage_Core_Model_Store::URL_TYPE_MEDIA, false)
        ;

        foreach ($collection as $product)
        {
            $resultProduct = array (
                'entity_id' => intval ($product->getId ())
            );

            foreach ($this->_attributeCodes as $code)
            {
                $resultProduct [$code] = $product->getData ($code);
            }

            foreach ($this->_descCodes as $code)
            {
                if (array_key_exists ($code, $resultProduct))
                {
                    $resultProduct [$code] = html_entity_decode (strip_tags ($resultProduct [$code]));
                }
            }

            foreach ($this->_imageCodes as $code)
            {
                $resultProduct [$code] = $mediaUrl . 'catalog/product' . $product->getData ($code); // no_cache
            }

            foreach ($this->_floatCodes as $code) $resultProduct [$code] = floatval ($resultProduct [$code]);

            foreach ($this->_intCodes as $code) $resultProduct [$code] = intval ($resultProduct [$code]);

            foreach ($this->_boolCodes as $code)
            {
                if (!strcmp ($code, 'free_shipping__'))
                {
                    foreach ($this->_freeshippingAttribute->getSource ()->getAllOptions () as $option)
                    {
                        if (!strcmp ($resultProduct [$code], $option ['value']))
                        {
                            $resultProduct [$code] = !strcmp ($option ['label'], 'Sim') ? true : false;
                        }
                    }
                }
                else
                {
                    $resultProduct [$code] = boolval ($resultProduct [$code]);
                }
            }

            $stockItem = $product->getStockItem ();

            if ($stockItem instanceof Mage_CatalogInventory_Model_Stock_Item)
            {
                $resultProduct ['stock_item'] = array(
                    'qty'                   => intval ($stockItem->getQty ()),
                    'min_sale_qty'          => intval ($stockItem->getMinSaleQty ()),
                    'max_sale_qty'          => intval ($stockItem->getMaxSaleQty ()),
                    'backorders'            => intval ($stockItem->getBackOrders ()),
                    'is_in_stock'           => boolval ($stockItem->getIsInStock ()),
                    'enable_qty_increments' => boolval ($stockItem->getEnableQtyIncrements ()),
                    'qty_increments'        => intval ($stockItem->getQtyIncrements ()),
                );
            }

            $resultProduct ['additional_attributes'] = array ();

            $resultProduct ['super_attributes'] = array ();

            if (!strcmp ($product->getTypeId (), Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE))
            {
                $resultProduct ['super_attributes'] = $product->getTypeInstance (true)->getConfigurableAttributesAsArray ($product);
            }

            $resultProduct ['website_ids'] = array_map (function ($n) { return intval ($n); }, $product->getWebsiteIds ());

            $resultProduct ['store_ids'] = array_map (function ($n) { return intval ($n); }, $product->getStoreIds ());

            $resultProduct ['cart_store'] = null;

            $resultProduct ['cart_wishlist'] = null;

            $resultProduct ['has_options']          = $product->getTypeInstance (true)->hasOptions ($product);
            $resultProduct ['has_required_options'] = $product->getTypeInstance (true)->hasRequiredOptions ($product);

            if (!empty ($resultProduct ['has_options']) || !empty ($resultProduct ['has_required_options']))
            {
                foreach ($product->getOptions () as $option)
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
                        // app
                        'selections'    => array (),
                        'values'        => array (),
                    );

                    foreach ($option->getValues () as $value)
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

                    $resultProduct ['options'][] = $resultOption;
                }
            }

            $resultProduct ['price_view'] = intval ($product->getPriceView ());
            $resultProduct ['price_type'] = intval ($product->getPriceType ());

            if (!strcmp ($product->getTypeId (), Mage_Catalog_Model_Product_Type::TYPE_BUNDLE))
            {
                $optionsCollection    = $product->getTypeInstance (true)->getOptionsCollection ($product);
                $selectionsCollection = $product->getTypeInstance (true)->getSelectionsCollection ($optionsCollection->getAllIds (), $product);

                foreach ($optionsCollection->appendSelections ($selectionsCollection) as $option)
                {
                    $resultOption = array(
                        'option_id'     => intval ($option->getId ()),
                        'product_id'    => intval ($option->getParentId ()),
                        'type'          => $option->getType (),
                        'is_require'    => boolval ($option->getRequired ()),
                        'sort_order'    => intval ($option->getPosition ()),
                        'default_title' => $option->getDefaultTitle (),
                        'title'         => $option->getTitle (),
                        // app
                        'selections'    => array (),
                        'values'        => array (),
                    );

                    foreach ($option->getSelections() as $selection)
                    {
                        if (!$selection->getIsSalable ()) continue;

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

                    $resultProduct ['bundle_options'][] = $resultOption;
                }
            }

            $resultProduct ['category_id']   = intval ($product->getData ('category_id'));
            $resultProduct ['category_name'] = $product->getData ('category_name');

            $result [] = $resultProduct;
        }

        return $result;
    }
}

