<?php
/**
 * @package     Gamuza_Mobile
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
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

/**
 * Product collection
 */
class Gamuza_Mobile_Model_Mysql4_Catalog_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Adding product custom options to result collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addOptionsToResult()
    {
        $productIds = array();

        foreach ($this as $product)
        {
            $productIds[] = $product->getId();
        }

        if (!empty($productIds))
        {
            $options = Mage::getModel('catalog/product_option')
                ->getCollection()
                ->addTitleToResult(Mage::app()->getStore()->getId())
                ->addPriceToResult(Mage::app()->getStore()->getId())
                ->addProductToFilter($productIds)
                // ->addValuesToResult()
            ;

            $options->getSelect()->order('sort_order ASC');

            $options->addValuesToResult();

            foreach ($options as $option)
            {
                if($this->getItemById($option->getProductId()))
                {
                    $this->getItemById($option->getProductId())->addOption($option);
                }
            }
        }

        return $this;
    }
}

