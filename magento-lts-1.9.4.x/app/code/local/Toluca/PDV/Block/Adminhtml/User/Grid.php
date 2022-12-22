<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_User_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('pdvUserGrid');
		$this->setDefaultSort ('entity_id');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
    }

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('pdv/user')->getCollection ();

		$this->setCollection ($collection);

		return parent::_prepareCollection ();
	}

	protected function _prepareColumns ()
	{
		$this->addColumn ('entity_id', array(
		    'header' => Mage::helper ('pdv')->__('ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'entity_id',
		));
		$this->addColumn ('item_id', array(
		    'header'  => Mage::helper ('pdv')->__('Cashier'),
		    'index'   => 'item_id',
            'type'    => 'options',
            'options' => self::getItems (),
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

    public static function getItems ()
    {
        $result = array ();

        foreach (Mage::getModel ('pdv/item')->getCollection () as $item)
        {
            $result [$item->getId ()] = sprintf ('%s - %s', $item->getId (), $item->getName ());
        }

        return $result;
    }
}

