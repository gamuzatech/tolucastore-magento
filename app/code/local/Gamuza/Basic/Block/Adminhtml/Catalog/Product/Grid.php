<?php
/**
 * @package     Gamuza_Basic
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
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

/**
 * Adminhtml customer grid block
 */
class Gamuza_Basic_Block_Adminhtml_Catalog_Product_Grid
    extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    protected $_isExport = true;

    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        $this->getCollection()->addAttributeToSelect ('thumbnail');

        $this->getCollection()->addAttributeToSelect ('weight');

        $this->getCollection()->addAttributeToSelect ('special_price');
/*
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory'))
        {
            $collection = $this->getCollection()->joinField(
                'is_in_stock',
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }
*/
        $this->getCollection ()->_addWebsiteNamesToResult ();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $store = $this->_getStore ();

        $this->removeColumn ('set_name');

        $this->addColumnAfter ('thumbnail', array(
            'header'   => Mage::helper ('basic')->__('Thumbnail'),
            'index'    => 'thumbnail',
            'media'    => 'catalog/product',
            'width'    => '75px',
            'filter'   => false,
            'sortable' => true,
            'renderer' => 'basic/adminhtml_widget_grid_column_renderer_image',
        ), 'entity_id');

        $this->addColumnsOrder ('entity_id', 'thumbnail');
/*
        $this->addColumnAfter ('weight', array(
            'header'   => Mage::helper ('basic')->__('Weight (Grams)'),
            'index'    => 'weight',
            'type'     => 'number',
        ), 'qty');
*/
        $this->addColumnAfter ('is_in_stock',
            array(
                'header'  => Mage::helper ('basic')->__('Is In Stock'),
                'index'   => 'is_in_stock',
                'width'   => '100px',
                'type'    => 'options',
                'options' => Mage::getModel ('adminhtml/system_config_source_yesno')->toArray (),
        ), 'qty');

        $this->addColumnAfter ('special_price',
            array(
                'header' => Mage::helper ('basic')->__('Special Price'),
                'index'  => 'special_price',
                'type'   => 'price',
                'currency_code' => $store->getBaseCurrency ()->getCode (),
        ), 'price');

        $this->addColumnAfter ('weight', array(
            'header'   => Mage::helper ('basic')->__('Weight'),
            'index'    => 'weight',
            'type'     => 'number',
        ), 'special_price');

        $this->addColumnAfter ('updated_at', array(
            'header'   => Mage::helper ('basic')->__('Updated At'),
            'index'    => 'updated_at',
            'type'     => 'datetime',
            'width'    => '100px',
        ), 'status');

        $this->sortColumnsByOrder ();
    }
}

