<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('erpProductGrid');
		$this->setDefaultSort ('entity_id');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
    }

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('erp/product')->getCollection ();

		$this->setCollection ($collection);

		return parent::_prepareCollection ();
	}

	protected function _prepareColumns ()
	{
		$this->addColumn ('entity_id', array(
		    'header' => Mage::helper ('erp')->__('ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'entity_id',
		));
        $this->addColumn ('type_id', array(
            'header'  => Mage::helper ('erp')->__('Type'),
            'index'   => 'type_id',
            'type'    => 'options',
            'options' => Mage::getModel ('catalog/product_type')->toOptionArray (),
        ));
        $this->addColumn ('product_id', array(
            'header' => Mage::helper ('erp')->__('Product ID'),
            'index'  => 'product_id',
            'type'   => 'number',
        ));
        $this->addColumn ('product_sku', array(
            'header' => Mage::helper ('erp')->__('Product SKU'),
            'index'  => 'product_sku',
        ));
		$this->addColumn ('company_id', array(
		    'header' => Mage::helper ('erp')->__('Company ID'),
		    'align'  => 'right',
		    'index'  => 'company_id',
		));
		$this->addColumn ('external_id', array(
		    'header' => Mage::helper ('erp')->__('Ext. ID'),
		    'align'  => 'right',
		    'index'  => 'external_id',
		));
		$this->addColumn ('external_sku', array(
		    'header' => Mage::helper ('erp')->__('Ext. SKU'),
		    'align'  => 'right',
		    'index'  => 'external_sku',
		));
		$this->addColumn ('name', array(
		    'header' => Mage::helper ('erp')->__('Name'),
		    'align'  => 'right',
		    'index'  => 'name',
		));
		$this->addColumn ('is_active', array(
		    'header'  => Mage::helper ('erp')->__('Is Active'),
		    'align'   => 'right',
		    'index'   => 'is_active',
            'type'    => 'options',
            'options' => Mage::getModel ('adminhtml/system_config_source_yesno')->toArray (),
		));
		$this->addColumn ('status', array(
		    'header'  => Mage::helper ('erp')->__('Status'),
		    'align'   => 'right',
		    'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getModel ('erp/adminhtml_system_config_source_status')->toArray (),
		));
		$this->addColumn ('message', array(
		    'header'  => Mage::helper ('erp')->__('Message'),
		    'index'   => 'message',
		));
		$this->addColumn ('updated_at', array(
			'header' => Mage::helper ('erp')->__('Updated At'),
			'index'  => 'updated_at',
            'type'   => 'datetime',
		));
		$this->addColumn ('synced_at', array(
			'header' => Mage::helper ('erp')->__('Synced At'),
			'index'  => 'synced_at',
            'type'   => 'datetime',
		));

        $this->addExportType ('*/*/exportCsv', Mage::helper ('erp')->__('CSV'));

        $this->addColumn('action', array(
            'header'  => Mage::helper('erp')->__('Action'),
            'width'   => '50px',
            'type'    => 'action',
            'getter'  => 'getProductId',
            'filter'   => false,
            'sortable' => false,
            'index'    => 'stores',
            'actions' => array(
                array(
                    'caption' => Mage::helper ('catalog')->__('Product'),
                    'field'   => 'id',
                    'url'     => array(
                        'base'   => 'adminhtml/catalog_product/edit',
                        'params' => array('store' => $this->getRequest()->getParam('store'))
                    ),
                )
            ),
        ));

		return parent::_prepareColumns ();
	}

	public function getRowUrl ($row)
	{
        // nothing here
	}

	protected function _prepareMassaction ()
	{
		$this->setMassactionIdField ('entity_id');
		$this->getMassactionBlock ()->setFormFieldName ('entity_ids')
		    ->setUseSelectAll (true)
		    ->addItem ('remove_products', array(
				 'label'   => Mage::helper ('erp')->__('Remove Products'),
				 'url'     => $this->getUrl ('*/adminhtml_product/massRemove'),
				 'confirm' => Mage::helper ('erp')->__('Are you sure?')
			))
        ;

		return $this;
	}
}

