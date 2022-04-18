<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Comanda_Adminhtml_ItemController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('toluca/comanda/item');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()
            ->_setActiveMenu ('toluca/comanda/item')
            ->_addBreadcrumb(
                Mage::helper ('comanda')->__('Items Manager'),
                Mage::helper ('comanda')->__('Items Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('Comanda'));
	    $this->_title ($this->__('Items Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}
}

