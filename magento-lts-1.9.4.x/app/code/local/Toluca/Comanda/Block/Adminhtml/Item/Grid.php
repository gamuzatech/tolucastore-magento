<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Comanda_Block_Adminhtml_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('comandaItemGrid');
		$this->setDefaultSort ('entity_id');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
    }

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('comanda/item')->getCollection ();

        $collection->getSelect ()
            ->join(
                array ('mesa' => Mage::getSingleton ('core/resource')->getTablename ('comanda/mesa')),
                'main_table.mesa_id = mesa.entity_id',
                array ('mesa_name' => 'mesa.name')
            )
        ;

        $mesaId = $this->getRequest ()->getParam ('mesa_id');

        if (intval ($mesaId) > 0)
        {
            $collection->addFieldToFilter ('mesa.entity_id', array ('eq' => $mesaId));
        }

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
		    'header' => Mage::helper ('comanda')->__('ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'entity_id',
            'filter_index' => 'main_table.entity_id',
		));
/*
		$this->addColumn ('mesa_id', array(
		    'header' => Mage::helper ('comanda')->__('Mesa ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'mesa_id',
		));
*/
		$this->addColumn ('mesa_name', array(
		    'header' => Mage::helper ('comanda')->__('Mesa Name'),
		    'index'  => 'mesa_name',
            'filter_index' => 'mesa.name',
		));
/*
		$this->addColumn ('order_id', array(
		    'header' => Mage::helper ('comanda')->__('Order ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'order_id',
		));
*/
		$this->addColumn ('order_increment_id', array(
		    'header' => Mage::helper ('comanda')->__('Order Inc. ID'),
		    'index'  => 'order_increment_id',
		));
		$this->addColumn ('product_id', array(
		    'header' => Mage::helper ('comanda')->__('Product ID'),
		    'align'  => 'right',
	        'type'   => 'number',
		    'index'  => 'product_id',
		));
		$this->addColumn ('sku', array(
		    'header' => Mage::helper ('comanda')->__('Sku'),
		    'index'  => 'sku',
		));
		$this->addColumn ('name', array(
		    'header' => Mage::helper ('comanda')->__('Name'),
		    'index'  => 'name',
            'filter_index' => 'main_table.name',
		));
		$this->addColumn ('price', array(
		    'header' => Mage::helper ('comanda')->__('Price'),
		    'index'  => 'price',
            'type'   => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
		$this->addColumn ('qty', array(
		    'header' => Mage::helper ('comanda')->__('Qty'),
		    'index'  => 'qty',
            'type'   => 'number',
		));
		$this->addColumn ('total', array(
		    'header' => Mage::helper ('comanda')->__('Total'),
		    'index'  => 'total',
            'type'   => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
		));
/*
        $this->addColumn ('options', array(
		    'header' => Mage::helper ('comanda')->__('Options'),
		    'index'  => 'options',
		));
        $this->addColumn ('additional_options', array(
		    'header' => Mage::helper ('comanda')->__('Additional Options'),
		    'index'  => 'additional_options',
		));
        $this->addColumn ('super_attribute', array(
		    'header' => Mage::helper ('comanda')->__('Super Attribute'),
		    'index'  => 'super_attribute',
		));
        $this->addColumn ('bundle_option', array(
		    'header' => Mage::helper ('comanda')->__('Bundle Option'),
		    'index'  => 'bundle_option',
		));
*/
		$this->addColumn ('created_at', array(
			'header' => Mage::helper ('comanda')->__('Created At'),
			'index'  => 'created_at',
            'type'   => 'datetime',
            'filter_index' => 'main_table.created_at',
		));
		$this->addColumn ('updated_at', array(
			'header' => Mage::helper ('comanda')->__('Updated At'),
			'index'  => 'updated_at',
            'type'   => 'datetime',
            'filter_index' => 'main_table.updated_at',
		));

        $this->addColumn ('mesa', array(
            // 'header'   => Mage::helper ('comanda')->__('Action'),
            'width'    => '50px',
            'type'     => 'action',
            'getter'   => 'getMesaId',
            'index'    => 'stores',
            'filter'   => false,
            'sortable' => false,
            'actions'  => array(
                array(
                    'caption' => Mage::helper ('comanda')->__('Mesa'),
                    'field'   => 'id',
                    'url'     => array(
                        'base'   => '*/adminhtml_mesa/edit',
                        'params' => array ('store' => $this->getRequest ()->getParam ('store'))
                    ),
                )
            ),
        ));

        $this->addColumn ('order', array(
            // 'header'   => Mage::helper ('comanda')->__('Action'),
            'width'    => '50px',
            'type'     => 'action',
            'getter'   => 'getOrderId',
            'index'    => 'stores',
            'filter'   => false,
            'sortable' => false,
            'actions'  => array(
                array(
                    'caption' => Mage::helper ('comanda')->__('Order'),
                    'field'   => 'order_id',
                    'url'     => array(
                        'base'   => 'adminhtml/sales_order/view',
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
}

