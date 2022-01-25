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
 * Bundle Type Model
 */
class Gamuza_Basic_Model_Bundle_Product_Type extends Mage_Bundle_Model_Product_Type
{
    /**
     * Before save type related data
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function beforeSave($product = null)
    {
        /*
        $product->setPriceType (Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC);
        */
        return parent::beforeSave($product);
    }

    /**
     * Save type related data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Bundle_Model_Product_Type
     */
    public function save ($product = null)
    {
        parent::save ($product);

        $write = Mage::getSingleton ('core/resource')->getConnection ('core_write');

        $collection = Mage::getResourceModel ('bundle/option_collection')
            ->setProductIdFilter ($product->getId ())
            ->joinValues (Mage_Core_Model_App::ADMIN_STORE_ID)
        ;

        $collection->getSelect ()
            ->reset (Zend_Db_Select::COLUMNS)
            ->columns (array ('option_id', 'parent_id'))
            ->order ('main_table.position')
            ->order ('option_value_default.title')
        ;

        $order = 0;

        foreach ($collection as $option)
        {
            $query = sprintf ('UPDATE catalog_product_bundle_option SET position = %s WHERE option_id = %s AND parent_id = %s LIMIT 1',
                ++ $order, $option->getOptionId (), $option->getParentId ()
            );

            $write->query ($query);

            /**
             * Selection
             */
            $selectionCollection = Mage::getResourceModel ('bundle/selection_collection')
                ->addAttributeToFilter ('name', array ('notnull' => true))
            ;

            $selectionCollection->getSelect ()
                ->where ('option_id = ?', $option->getOptionId ())
                ->where ('parent_product_id = ?', $option->getParentId ())
                ->reset (Zend_Db_Select::COLUMNS)
                ->columns (array(
                    'selection_id'      => 'selection.selection_id',
                    'option_id'         => 'selection.option_id',
                    'parent_product_id' => 'selection.parent_product_id',
                    'product_id'        => 'selection.product_id'
                ))
                ->order ('selection.position')
                ->order ('at_name.value')
            ;

            $selectionOrder = 0;

            foreach ($selectionCollection as $selection)
            {
                $query = sprintf ('UPDATE catalog_product_bundle_selection SET position = %s WHERE selection_id = %s AND option_id = %s AND product_id = %s LIMIT 1',
                    ++ $selectionOrder, $selection->getSelectionId (), $selection->getOptionId (), $selection->getProductId ()
                );

                $write->query ($query);
            }
        }

        return $this;
    }
}

