<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2023 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Adminhtml_TotalController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('toluca/pdv/total');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()->_setActiveMenu ('pdv/total')
            ->_addBreadcrumb(
                Mage::helper ('pdv')->__('Totals Manager'),
                Mage::helper ('pdv')->__('Totals Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('PDV'));
	    $this->_title ($this->__('Totals Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}
}

