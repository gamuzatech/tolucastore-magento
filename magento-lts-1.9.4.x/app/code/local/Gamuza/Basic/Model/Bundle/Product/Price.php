<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2019 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

/**
 * Bundle Price Model
 */
class Gamuza_Basic_Model_Bundle_Product_Price extends Mage_Bundle_Model_Product_Price
{
    const PRICE_RANGE    = Gamuza_Basic_Helper_Data::PRODUCT_PRICE_VIEW_PRICE_RANGE;

    const AS_LOW_AS      = Gamuza_Basic_Helper_Data::PRODUCT_PRICE_VIEW_AS_LOW_AS;
    const AS_HIGH_AS     = Gamuza_Basic_Helper_Data::PRODUCT_PRICE_VIEW_AS_HIGH_AS;

    const AS_LOW_AS_ONE  = Gamuza_Basic_Helper_Data::PRODUCT_PRICE_VIEW_AS_LOW_AS_ONE;
    const AS_HIGH_AS_ONE = Gamuza_Basic_Helper_Data::PRODUCT_PRICE_VIEW_AS_HIGH_AS_ONE;

    const PRICE_STATIC   = Gamuza_Basic_Helper_Data::PRODUCT_PRICE_VIEW_PRICE_STATIC;
    const PRICE_AVERAGE  = Gamuza_Basic_Helper_Data::PRODUCT_PRICE_VIEW_PRICE_AVERAGE;

    /**
     * Get Total price  for Bundle items
     *
     * @param Mage_Catalog_Model_Product $product
     * @param null|float $qty
     * @return float
     */
    public function getTotalBundleItemsPrice($product, $qty = null)
    {
        if (!strcmp ($product->getPriceView (), self::PRICE_STATIC))
        {
            return $product->getData ('price');
        }

        if (in_array ($product->getPriceView (), array(
            self::PRICE_RANGE, self::AS_LOW_AS, self::AS_HIGH_AS,
        )))
        {
            return parent::getTotalBundleItemsPrice ($product, $qty);
        }

        $price = $product->getPriceView () == self::AS_LOW_AS_ONE ? PHP_INT_MAX : 0.0;

        if ($product->hasCustomOptions())
        {
            $customOption = $product->getCustomOption('bundle_selection_ids');

            if ($customOption)
            {
                $selectionIds = unserialize($customOption->getValue());

                $selections = $product->getTypeInstance(true)->getSelectionsByIds($selectionIds, $product);
                $selections->addTierPriceData();

                Mage::dispatchEvent('prepare_catalog_product_collection_prices', array(
                    'collection' => $selections,
                    'store_id' => $product->getStoreId(),
                ));

                foreach ($selections->getItems() as $selection)
                {
                    if ($selection->isSalable())
                    {
                        $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());

                        if ($selectionQty)
                        {
                            $result = $this->getSelectionFinalTotalPrice($product, $selection, $qty,
                                $selectionQty->getValue());

                            if ($product->getPriceView () == self::PRICE_AVERAGE)
                            {
                                $price += $result;

                                continue;
                            }

                            $operator = $product->getPriceView () == self::AS_LOW_AS_ONE ? '<' : '>';

                            if (version_compare ($result, $price, $operator)) $price = $result;
                        }
                    }
                }

                if ($product->getPriceView () == self::PRICE_AVERAGE)
                {
                    $selectionsQty = count($selections->getItems());
                    $price = $price / $selectionsQty;
                }
            }
        }

        return $price;
    }
}

