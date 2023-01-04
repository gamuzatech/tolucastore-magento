<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_Cashier_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
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
        $customerEmail = Mage::helper ('pdv')->getCustomerEmail ('%');

		$collection = Mage::getModel ('pdv/cashier')->getCollection ();

        $collection->getSelect ()
            ->joinLeft (
                array ('total' => Mage::getSingleton ('core/resource')->getTableName ('pdv/total')),
                'main_table.total_id = total.entity_id',
                array (
                    'opened_at',
                    'closed_at',
                )
            )
            ->joinLeft (
                array ('quote' => Mage::getSingleton ('core/resource')->getTableName ('sales/quote')),
                "main_table.entity_id = quote.pdv_cashier_id AND quote.is_pdv = 1 AND quote.pdv_operator_id = main_table.operator_id AND quote.customer_email LIKE '{$customerEmail}'",
                array (
                    'customer_id' => 'GROUP_CONCAT(quote.pdv_customer_id)',
                    'quote_id'    => 'GROUP_CONCAT(quote.entity_id)',
                )
            )
            ->group ('main_table.entity_id')
        ;

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
        $store = $this->_getStore();

		$this->addColumn ('entity_id', array(
		    'header' => Mage::helper ('pdv')->__('ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'entity_id',
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

		$this->addColumn ('opened_at', array(
			'header' => Mage::helper ('pdv')->__('Opened At'),
			'index'  => 'opened_at',
            'type'   => 'datetime',
            'filter_index' => 'total.opened_at',
		));
		$this->addColumn ('closed_at', array(
			'header' => Mage::helper ('pdv')->__('Closed At'),
			'index'  => 'closed_at',
            'type'   => 'datetime',
            'filter_index' => 'total.closed_at',
		));

		$this->addColumn ('operator_id', array(
		    'header'  => Mage::helper ('pdv')->__('Operator'),
		    'align'   => 'right',
	        'type'    => 'number',
		    'index'   => 'operator_id',
            'type'    => 'options',
            'options' => self::getOperators (),
		));
		$this->addColumn ('total_id', array(
		    'header'  => Mage::helper ('pdv')->__('Total ID'),
		    'align'   => 'right',
	        'type'    => 'number',
		    'index'   => 'total_id',
		));
		$this->addColumn ('customer_id', array(
		    'header' => Mage::helper ('pdv')->__('Customer ID'),
		    'index'  => 'customer_id',
            'filter_index' => 'quote.customer_id',
		));
		$this->addColumn ('quote_id', array(
		    'header' => Mage::helper ('pdv')->__('Quote ID'),
		    'index'  => 'quote_id',
            'filter_index' => 'quote.entity_id',
		));

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

