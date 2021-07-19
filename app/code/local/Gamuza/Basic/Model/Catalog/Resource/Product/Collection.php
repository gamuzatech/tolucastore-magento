<?php
/*
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2018 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * Product collection
 */
class Gamuza_Basic_Model_Catalog_Resource_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{
    public function addWebsiteNamesToResult()
    {
        // nothing
    }

    public function joinField ($alias, $table, $field, $bind, $cond = null, $joinType = 'inner')
    {
        if (!strcmp ($alias, 'qty') && Mage::helper ('catalog')->isModuleEnabled ('Mage_CatalogInventory'))
        {
            $this->joinField(
                'is_in_stock',
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }

        return parent::joinField ($alias, $table, $field, $bind, $cond, $joinType);
    }

    public function _addWebsiteNamesToResult()
    {
        return Mage_Catalog_Model_Resource_Product_Collection::addWebsiteNamesToResult();
    }
}

