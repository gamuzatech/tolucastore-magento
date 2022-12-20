<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_countTotals = true;

    protected $_isExport = true;

    public $_fieldsTotals = array(
        'opened_amount' => 0,
        'reinforced_amount' => 0,
        'bleeded_amount' => 0,
        'money_amount' => 0,
        'changed_amount' => 0,
        'closed_amount' => 0,
    );

	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('pdvItemGrid');
		$this->setDefaultSort ('entity_id');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
    }

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('pdv/item')->getCollection ();

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
            'options' => Mage::getModel ('pdv/adminhtml_system_config_source_item_status')->toArray (),
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
		$this->addColumn ('user_id', array(
		    'header'  => Mage::helper ('pdv')->__('User'),
		    'align'   => 'right',
	        'type'    => 'number',
		    'index'   => 'user_id',
            'type'    => 'options',
            'options' => self::getUsers (),
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
		$this->addColumn ('opened_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Opened Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'opened_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('reinforced_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Reinforced Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'reinforced_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('bleeded_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Bleeded Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'bleeded_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('money_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Money Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'money_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('changed_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Changed Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'changed_amount',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('closed_amount', array(
		    'header'  => Mage::helper ('pdv')->__('Closed Amount'),
		    'align'   => 'right',
	        'type'    => 'price',
		    'index'   => 'closed_amount',
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

    public static function getUsers ()
    {
        $result = array ();

        foreach (Mage::getModel ('pdv/user')->getCollection () as $user)
        {
            $result [$user->getId ()] = sprintf ('%s - %s', $user->getId (), $user->getName ());
        }

        return $result;
    }
}

