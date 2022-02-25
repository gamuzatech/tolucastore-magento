<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('erp/customer');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()->_setActiveMenu ('erp/customer')
            ->_addBreadcrumb(
                Mage::helper ('erp')->__('Customers Manager'),
                Mage::helper ('erp')->__('Customers Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('ERP'));
	    $this->_title ($this->__('Customers Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'customers.csv';

        $content = $this->getLayout()
            ->createBlock('erp/adminhtml_customer_grid')
            ->getCsvFile()
        ;

        $this->_prepareDownloadResponse ($fileName, $content);
    }

	public function massRemoveAction ()
	{
		try
        {
			$ids = $this->getRequest ()->getPost ('entity_ids', array ());

			foreach ($ids as $id)
            {
                $model = Mage::getModel('erp/customer');

                $model->setId ($id)->delete ();
			}

			Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('erp')->__('Customers were successfully removed.'));
		}
		catch (Exception $e)
        {
			Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());
		}

		$this->_redirect('*/*/');
	}
}

