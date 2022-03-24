<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct ()
    {
        parent::__construct ();

        $this->setId ('mercadolivreProductsGrid');
        $this->setDefaultSort ('entity_id');
        $this->setDefaultDir ('DESC');
        $this->setSaveParametersInSession (true);
    }

    protected function _prepareCollection ()
    {
        $collection = Mage::getModel ('mercadolivre/product')->getCollection ();

        $collection->getSelect ()->joinLeft(
            array ('product' => Mage::getSingleton ('core/resource')->getTableName ('catalog/product')),
            'main_table.product_id = product.entity_id',
            array(
                'type_id' => 'product.type_id',
                'sku'     => 'product.sku',
            )
        );

        $this->setCollection ($collection);

        return parent::_prepareCollection ();
    }

    protected function _prepareColumns()
    {
        $this->addColumn ('entity_id', array(
            'header' => Mage::helper ('mercadolivre')->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'entity_id',
            'filter_index' => 'main_table.entity_id',
        ));
        $this->addColumn ('product_id', array(
            'header' => Mage::helper ('mercadolivre')->__('Product ID'),
            'index'  => 'product_id',
            'type'   => 'number',
        ));
        $this->addColumn ('type_id', array(
            'header'  => Mage::helper ('mercadolivre')->__('Type'),
            'index'   => 'type_id',
            'type'    => 'options',
            'options' => Mage::getModel ('catalog/product_type')->getOptionArray (),
        ));
        $this->addColumn ('sku', array(
            'header' => Mage::helper ('mercadolivre')->__('SKU'),
            'index'  => 'sku',
        ));
        $this->addColumn ('external_id', array(
            'header' => Mage::helper ('mercadolivre')->__('External ID'),
            'index'  => 'external_id',
        ));
        $this->addColumn ('seller_id', array(
            'header' => Mage::helper ('mercadolivre')->__('Seller ID'),
            'index'  => 'seller_id',
        ));
        $this->addColumn ('category_id', array(
            'header' => Mage::helper ('mercadolivre')->__('Category ID'),
            'index'  => 'category_id',
        ));
        $this->addColumn ('category_name', array(
            'header' => Mage::helper ('mercadolivre')->__('Category Name'),
            'index'  => 'category_name',
        ));
        $this->addColumn ('status', array(
            'header'  => Mage::helper ('mercadolivre')->__('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getModel ('mercadolivre/adminhtml_system_config_source_queue_status')->toArray (),
        ));
        $this->addColumn ('message', array(
            'header' => Mage::helper ('mercadolivre')->__('Message'),
            'index'  => 'message',
        ));
        $this->addColumn ('updated_at', array(
            'header' => Mage::helper ('mercadolivre')->__('Updated At'),
            'index'  => 'updated_at',
            'type'   => 'datetime',
            'width'  => '100px',
            'filter_index' => 'main_table.updated_at',
        ));
        $this->addColumn ('synced_at', array(
            'header' => Mage::helper ('mercadolivre')->__('Synced At'),
            'index'  => 'synced_at',
            'type'   => 'datetime',
            'width'  => '100px',
        ));

        $this->addColumn ('action', array(
            'header'   => Mage::helper('mercadolivre')->__('Action'),
            'width'    => '50px',
            'type'     => 'action',
            'getter'   => 'getProductId',
            'filter'   => false,
            'sortable' => false,
            'index'    => 'stores',
            'actions'  => array(
                array(
                    'caption' => Mage::helper ('catalog')->__('Edit'),
                    'field'   => 'id',
                    'url'     => array(
                        'base'   => 'adminhtml/catalog_product/edit',
                        'params' => array ('store' => $this->getRequest ()->getParam ('store'))
                    ),
                )
            ),
        ));

        return parent::_prepareColumns ();
    }

    public function getRowUrl ($row)
    {
        // nothing_here
    }

    protected function _prepareMassaction ()
    {
        $this->setMassactionIdField ('entity_id');
        $this->getMassactionBlock ()->setFormFieldName ('entity_ids');
        $this->getMassactionBlock ()->setUseSelectAll (true);

        $this->getMassactionBlock()->addItem ('remove_products', array(
            'label'   => Mage::helper ('mercadolivre')->__('Remove Products'),
            'url'     => $this->getUrl ('*/*/massRemove'),
            'confirm' => Mage::helper ('mercadolivre')->__('Are you sure?')
        ));

        return parent::_prepareMassaction ();
    }
}

