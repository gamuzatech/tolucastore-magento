<?php
/**
 * @package     Gamuza_Basic
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
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

/**
 * Bundle Price Type Attribute Renderer
 */
class Gamuza_Basic_Model_Bundle_Product_Attribute_Source_Price_Type
    extends Mage_Bundle_Model_Product_Attribute_Source_Price_View
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options))
        {
            $this->_options = array(
                array(
                    'label' => Mage::helper('bundle')->__('Dynamic'),
                    'value' => Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC
                ),
                array(
                    'label' => Mage::helper('bundle')->__('Fixed'),
                    'value' => Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED
                ),
            );
        }

        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        $column = array(
            'unsigned'  => false,
            'default'   => null,
            'extra'     => null
        );

        if (Mage::helper('core')->useDbCompatibleMode())
        {
            $column['type']     = 'int';
            $column['is_null']  = true;
        }
        else
        {
            $column['type']     = Varien_Db_Ddl_Table::TYPE_INTEGER;
            $column['nullable'] = true;
            $column['comment']  = 'Bundle Price Type ' . $attributeCode . ' column';
        }

        return array($attributeCode => $column);
   }
}

