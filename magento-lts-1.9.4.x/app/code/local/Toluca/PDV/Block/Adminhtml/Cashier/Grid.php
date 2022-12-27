<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_Cashier_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_countTotals = true;

    protected $_isExport = true;

    public $_fieldsTotals = array(
        'open_amount' => 0,
        'reinforce_amount' => 0,
        'bleed_amount' => 0,
        'money_amount' => 0,
        'change_amount' => 0,
        'close_amount' => 0,
    );

	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('pdvCashierGrid');
		$this->setDefaultSort ('entity_id');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
    }

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('pdv/cashier')->getCollection ();

		$this->setCollection ($collection);

		return parent::_prepareCollection ();
	}

    public function getTotals ()
    {
        return $this->helper ('pdv')->getTotals ($this);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);

        return Mage::app()->getStore($storeId);
    }

	protected function _prepareColumns ()
	{
        $store = $this->_getStore();

		$this->addColumn ('entity_id', array(
		    'header' => Mage::helper ('pdv')->__('ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'entity_id',
		));
		$this->addColumn ('quote_id', array(
		    'header' => Mage::helper ('pdv')->__('Quote ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'quote_id',
		));
		$this->addColumn ('code', array(
		    'header'  => Mage::helper ('pdv')->__('Code'),
		    'index'   => 'code',
		));
		$this->addColumn ('name', array(
		    'header'  => Mage::helper ('pdv')->__('Name'),
		    'index'   => 'name',
		));
		$this->addColumn ('is_active', array(
		    'header'  => Mage::helper ('pdv')->__('Is Active'),
		    'index'   => 'is_active',
            'type'    => 'options',
            'options' => Mage::getModel ('adminhtml/system_config_source_yesno')->toArray (),
		));
		$this->addColumn ('status', array(
		    'header'  => Mage::helper ('pdv')->__('Status'),
		    'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getModel ('pdv/adminhtml_system_config_source_cashier_status')->toArray (),
		));
/*
		$this->addColumn ('created_at', array(
			'header' => Mage::helper ('pdv')->__('Created At'),
			'index'  => 'created_at',
            'type'   => 'datetime',
		));
		$this->addColumn ('updated_at', array(
			'header' => Mage::helper ('pdv')->__('Updated At'),
			'index'  => 'updated_at',
            'type'   => 'datetime',
		));
*/
		$this->addColumn ('operator_id', array(
		    'header'  => Mage::helper ('pdv')->__('Operator'),
		    'align'   => 'right',
	        'type'    => 'number',
		    'index'   => 'operator_id',
            'type'    => 'options',
            'options' => self::getOperators (),
		));
		$this->addColumn ('opened_at', array(
			'header' => Mage::helper ('pdv')->__('Opened At'),
			'index'  => 'opened_at',
            'type'   => 'datetime',
		));
		$this->addColumn ('closed_at', array(
			'header' => Mage::helper ('pdv')->__('Closed At'),
			'index'  => 'closed_at',
            'type'   => 'datetime',
		));
		$this->addColumn ('open_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Open Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'open_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('reinforce_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Reinforce Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'reinforce_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('bleed_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Bleed Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'bleed_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('money_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Money Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'money_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('change_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Change Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'change_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('close_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Close Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'close_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));

        $this->addColumn ('action', array(
            'header'   => Mage::helper ('pdv')->__('Action'),
            'width'    => '50px',
            'type'     => 'action',
            'getter'   => 'getId',
            'index'    => 'stores',
            'filter'   => false,
            'sortable' => false,
            'actions'  => array(
                array(
                    'caption' => Mage::helper ('pdv')->__('Edit'),
                    'field'   => 'id',
                    'url'     => array(
                        'base'   => '*/*/edit',
                        'params' => array ('store' => $this->getRequest ()->getParam ('store'))
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

    public static function getOperators ()
    {
        $result = array ();

        foreach (Mage::getModel ('pdv/operator')->getCollection () as $operator)
        {
            $result [$operator->getId ()] = sprintf ('%s - %s', $operator->getId (), $operator->getName ());
        }

        return $result;
    }
}

