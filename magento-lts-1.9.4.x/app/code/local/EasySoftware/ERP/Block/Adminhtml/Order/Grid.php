<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('erpOrderGrid');
		$this->setDefaultSort ('entity_id');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
    }

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('erp/order')->getCollection ();

		$this->setCollection ($collection);

		return parent::_prepareCollection ();
	}

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);

        return Mage::app()->getStore($storeId);
    }

	protected function _prepareColumns ()
	{
        $store = $this->_getStore ();

		$this->addColumn ('entity_id', array(
		    'header' => Mage::helper ('erp')->__('ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'entity_id',
		));
/*
        $this->addColumn ('customer_id', array(
            'header'  => Mage::helper ('erp')->__('Customer'),
            'index'   => 'customer_id',
            'type'    => 'number',
        ));
*/
        $this->addColumn ('customer_email', array(
            'header'  => Mage::helper ('erp')->__('Customer Email'),
            'index'   => 'customer_email',
        ));
        $this->addColumn ('customer_taxvat', array(
            'header'  => Mage::helper ('erp')->__('Customer Taxvat'),
            'index'   => 'customer_taxvat',
        ));
        $this->addColumn ('customer_name', array(
            'header'  => Mage::helper ('erp')->__('Customer Name'),
            'index'   => 'customer_name',
        ));
/*
        $this->addColumn ('order_id', array(
            'header' => Mage::helper ('erp')->__('Order ID'),
            'index'  => 'order_id',
            'type'   => 'number',
        ));
*/
        $this->addColumn ('order_increment_id', array(
            'header' => Mage::helper ('erp')->__('Order Inc. ID'),
            'index'  => 'order_increment_id',
        ));
        $this->addColumn ('order_base_grand_total', array(
            'header' => Mage::helper ('erp')->__('G.T. (Base)'),
            'index'  => 'order_base_grand_total',
            'type'  => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
        ));
		$this->addColumn ('company_id', array(
		    'header' => Mage::helper ('erp')->__('Comp. ID'),
		    'align'  => 'right',
		    'index'  => 'company_id',
		));
		$this->addColumn ('external_id', array(
		    'header' => Mage::helper ('erp')->__('Ext. ID'),
		    'align'  => 'right',
		    'index'  => 'external_id',
		));
/*
		$this->addColumn ('external_code', array(
		    'header' => Mage::helper ('erp')->__('Ext. Code'),
		    'align'  => 'right',
		    'index'  => 'external_code',
		));
*/
		$this->addColumn ('is_canceled', array(
		    'header'  => Mage::helper ('erp')->__('Cancel'),
		    'align'   => 'right',
		    'index'   => 'is_canceled',
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
            'getter'  => 'getOrderId',
            'filter'   => false,
            'sortable' => false,
            'index'    => 'stores',
            'actions' => array(
                array(
                    'caption' => Mage::helper ('catalog')->__('Order'),
                    'field'   => 'order_id',
                    'url'     => array(
                        'base'   => 'adminhtml/sales_order/view',
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
		    ->addItem ('remove_orders', array(
				 'label'   => Mage::helper ('erp')->__('Remove Orders'),
				 'url'     => $this->getUrl ('*/adminhtml_order/massRemove'),
				 'confirm' => Mage::helper ('erp')->__('Are you sure?')
			))
        ;

		return $this;
	}
}

