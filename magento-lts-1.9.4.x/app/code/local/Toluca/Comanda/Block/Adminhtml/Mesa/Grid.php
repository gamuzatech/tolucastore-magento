<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Comanda_Block_Adminhtml_Mesa_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('comandaMesaGrid');
		$this->setDefaultSort ('entity_id');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
    }

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('comanda/mesa')->getCollection ();

		$this->setCollection ($collection);

		return parent::_prepareCollection ();
	}

	protected function _prepareColumns ()
	{
		$this->addColumn ('entity_id', array(
		    'header' => Mage::helper ('comanda')->__('ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'entity_id',
		));
		$this->addColumn ('name', array(
		    'header' => Mage::helper ('comanda')->__('Name'),
		    'index'  => 'name',
		));
		$this->addColumn ('description', array(
		    'header' => Mage::helper ('comanda')->__('Description'),
		    'index'  => 'description',
		));
		$this->addColumn ('is_active', array(
		    'header'  => Mage::helper ('comanda')->__('Is Active'),
		    'index'   => 'is_active',
            'type'    => 'options',
            'options' => Mage::getModel ('adminhtml/system_config_source_yesno')->toArray (),
		));
		$this->addColumn ('status', array(
		    'header'  => Mage::helper ('comanda')->__('Status'),
		    'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getModel ('comanda/adminhtml_system_config_source_mesa_status')->toArray (),
		));
		$this->addColumn ('created_at', array(
			'header' => Mage::helper ('comanda')->__('Created At'),
			'index'  => 'created_at',
            'type'   => 'datetime',
		));
		$this->addColumn ('updated_at', array(
			'header' => Mage::helper ('comanda')->__('Updated At'),
			'index'  => 'updated_at',
            'type'   => 'datetime',
		));

        $this->addColumn ('action', array(
            'header'   => Mage::helper ('comanda')->__('Action'),
            'width'    => '50px',
            'type'     => 'action',
            'getter'   => 'getId',
            'index'    => 'stores',
            'filter'   => false,
            'sortable' => false,
            'actions'  => array(
                array(
                    'caption' => Mage::helper ('comanda')->__('Edit'),
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

    protected function _prepareMassaction ()
    {
        $this->setMassactionIdField ('entity_id');
        $this->getMassactionBlock ()->setFormFieldName ('mesa');

        $statuses = Mage::getSingleton ('adminhtml/system_config_source_yesno')->toOptionArray ();

        array_unshift ($statuses, array ('label' => '', 'value' => ''));

        $this->getMassactionBlock ()->addItem ('status', array(
            'label' => Mage::helper ('adminhtml')->__('Change status'),
            'url'   => $this->getUrl ('*/*/massStatus', array ('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => Mage::helper ('adminhtml')->__('Enabled'),
                    'values' => $statuses
                )
            )
        ));

        return parent::_prepareMassaction ();
    }

	public function getRowUrl ($row)
	{
        // nothing here
	}
}

