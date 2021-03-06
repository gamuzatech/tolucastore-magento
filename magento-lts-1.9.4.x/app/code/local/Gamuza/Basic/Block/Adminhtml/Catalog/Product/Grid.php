<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2017 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
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

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $this->getMassactionBlock()->addItem('stock_qty', array(
             'label'=> Mage::helper('basic')->__('Change Stock Qty'),
             'url'  => $this->getUrl('*/*/massStockQty', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'stock_qty',
                         'type' => 'text',
                         'class' => 'required-entry validate-digits',
                         'label' => Mage::helper('basic')->__('Qty'),
                     )
             )
        ));

        $statuses = array(
            array (),
            array ('value' => Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK, 'label' => Mage::helper ('catalogInventory')->__('Out of Stock')),
            array ('value' => Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK,     'label' => Mage::helper ('catalogInventory')->__('In Stock'))
        );

        $this->getMassactionBlock()->addItem('stock_status', array(
             'label'=> Mage::helper('basic')->__('Change Stock Status'),
             'url'  => $this->getUrl('*/*/massStockStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'stock_status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('basic')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        $this->getMassactionBlock()->addItem('price', array(
             'label'=> Mage::helper('basic')->__('Change Price'),
             'url'  => $this->getUrl('*/*/massPrice', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'price',
                         'type' => 'text',
                         'class' => 'required-entry validate-number validate-greater-than-zero',
                         'label' => Mage::helper('basic')->__('Price'),
                     )
             )
        ));

        $this->getMassactionBlock()->addItem('special_price', array(
             'label'=> Mage::helper('basic')->__('Change Special Price'),
             'url'  => $this->getUrl('*/*/massSpecialPrice', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'special_price',
                         'type' => 'text',
                         'class' => 'required-entry validate-number validate-zero-or-greater',
                         'label' => Mage::helper('basic')->__('Special Price'),
                     )
             )
        ));

        $this->getMassactionBlock()->addItem('weight', array(
             'label'=> Mage::helper('basic')->__('Change Weight'),
             'url'  => $this->getUrl('*/*/massWeight', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'weight',
                         'type' => 'text',
                         'class' => 'required-entry validate-number validate-zero-or-greater',
                         'label' => Mage::helper('basic')->__('Weight'),
                     )
             )
        ));

        $visibilities = Mage::getModel('catalog/product_visibility')->getOptionArray();

        array_unshift ($visibilities, array('label'=>'', 'value'=>''));

        $this->getMassactionBlock()->addItem('visibility', array(
             'label'=> Mage::helper('basic')->__('Change Visibility'),
             'url'  => $this->getUrl('*/*/massVisibility', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'visibility',
                         'type' => 'select',
                         'class' => 'required-entry validate-select',
                         'label' => Mage::helper('basic')->__('Visibility'),
                         'values' => $visibilities,
                     )
             )
        ));
    }
}

