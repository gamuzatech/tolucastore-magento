<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Bot_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('botQueueGrid');
		$this->setDefaultSort ('updated_at');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
    }

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('bot/queue')->getCollection ();

        $collection->getSelect ()->joinLeft (
            array ('order' => Mage::getSingleton ('core/resource')->getTableName ('sales/order')),
            'main_table.order_id = order.entity_id',
            array ('increment_id')
        );

		$this->setCollection ($collection);

		return parent::_prepareCollection ();
	}

	protected function _prepareColumns ()
	{
        $categories = array ();

        foreach (Mage::getModel ('catalog/category')->getCollection ()->addNameToResult () as $category)
        {
            $categories [$category->getId ()] = str_repeat (' - ', intval ($category->getLevel ())) . $category->getName ();
        }

        foreach (Mage::getModel ('catalog/product')->getCollection ()->addAttributeToSelect ('name') as $product)
        {
            $products [$product->getId ()] = $product->getName ();
        }

		$this->addColumn ('entity_id', array(
		    'header' => Mage::helper ('bot')->__('ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'entity_id',
            'filter_index' => 'main_table.entity_id',
		));
		$this->addColumn ('increment_id', array(
		    'header' => Mage::helper ('bot')->__('Order'),
		    'align'  => 'right',
		    'index'  => 'increment_id',
		));
		$this->addColumn ('category_id', array(
		    'header'  => Mage::helper ('bot')->__('Category'),
		    'align'   => 'right',
		    'index'   => 'category_id',
            'type'    => 'options',
            'options' => $categories,
		));
		$this->addColumn ('product_id', array(
		    'header'  => Mage::helper ('bot')->__('Product'),
		    'align'   => 'right',
		    'index'   => 'product_id',
            'type'    => 'options',
            'options' => $products,
		));
		$this->addColumn ('product_options', array(
		    'header'  => Mage::helper ('bot')->__('Options'),
		    'align'   => 'right',
		    'index'   => 'product_options',
		));
		$this->addColumn ('product_comment', array(
		    'header'  => Mage::helper ('bot')->__('Comment'),
		    'align'   => 'right',
		    'index'   => 'product_comment',
		));
		$this->addColumn ('number', array(
		    'header'  => Mage::helper ('bot')->__('Number'),
		    'index'   => 'number',
		));
		$this->addColumn ('firstname', array(
		    'header'  => Mage::helper ('bot')->__('Firstname'),
            'align'   => 'right',
		    'index'   => 'firstname',
		));
		$this->addColumn ('lastname', array(
		    'header'  => Mage::helper ('bot')->__('Lastname'),
            'align'   => 'right',
		    'index'   => 'lastname',
		));
		$this->addColumn ('status', array(
		    'header'  => Mage::helper ('bot')->__('Status'),
		    'align'   => 'right',
		    'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getModel ('bot/adminhtml_system_config_source_queue_status')->toArray (),
            'filter_index' => 'main_table.status',
		));
/*
		$this->addColumn ('message', array(
		    'header'  => Mage::helper ('bot')->__('Message'),
		    'index'   => 'message',
		));
*/
		$this->addColumn ('is_muted', array(
		    'header'  => Mage::helper ('bot')->__('Is Muted'),
		    'align'   => 'right',
		    'index'   => 'is_muted',
	        'type'    => 'options',
            'options' => Mage::getModel ('adminhtml/system_config_source_yesno')->toArray (),
		));
		$this->addColumn ('is_notified', array(
		    'header'  => Mage::helper ('bot')->__('Is Notified'),
		    'align'   => 'right',
		    'index'   => 'is_notified',
	        'type'    => 'options',
            'options' => Mage::getModel ('adminhtml/system_config_source_yesno')->toArray (),
		));
		$this->addColumn ('created_at', array(
			'header' => Mage::helper ('bot')->__('Created At'),
			'index'  => 'created_at',
            'type'   => 'datetime',
            'filter_index' => 'main_table.created_at',
		));
		$this->addColumn ('updated_at', array(
			'header' => Mage::helper ('bot')->__('Updated At'),
			'index'  => 'updated_at',
            'type'   => 'datetime',
            'filter_index' => 'main_table.updated_at',
		));

        $this->addExportType ('*/*/exportCsv', Mage::helper ('bot')->__('CSV'));

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
		    ->addItem ('remove_items', array(
				 'label'   => Mage::helper ('bot')->__('Remove Items'),
				 'url'     => $this->getUrl ('*/adminhtml_queue/massRemove'),
				 'confirm' => Mage::helper ('bot')->__('Are you sure?')
			))
        ;

		return $this;
	}
}

