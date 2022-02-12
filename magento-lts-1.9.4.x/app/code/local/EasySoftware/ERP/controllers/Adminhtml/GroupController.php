<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Adminhtml_GroupController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('erp/group');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()->_setActiveMenu ('erp/group')
            ->_addBreadcrumb(
                Mage::helper ('erp')->__('Groups Manager'),
                Mage::helper ('erp')->__('Groups Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('ERP'));
	    $this->_title ($this->__('Groups Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'groups.csv';

        $content = $this->getLayout()
            ->createBlock('erp/adminhtml_group_grid')
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
                $model = Mage::getModel('erp/group');

                $model->setId ($id)->delete ();
			}

			Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('erp')->__('Groups were successfully removed.'));
		}
		catch (Exception $e)
        {
			Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());
		}

		$this->_redirect('*/*/');
	}
}

