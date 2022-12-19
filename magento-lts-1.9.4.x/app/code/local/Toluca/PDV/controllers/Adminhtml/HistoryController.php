<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Adminhtml_HistoryController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('toluca/pdv/history');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()->_setActiveMenu ('pdv/history')
            ->_addBreadcrumb(
                Mage::helper ('pdv')->__('History Manager'),
                Mage::helper ('pdv')->__('History Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('PDV'));
	    $this->_title ($this->__('History Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}
}

