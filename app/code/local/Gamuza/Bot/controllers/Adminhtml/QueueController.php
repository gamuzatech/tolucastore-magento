<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Bot_Adminhtml_QueueController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('gamuza/bot/queue');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()->_setActiveMenu ('gamuza/bot/queue')
            ->_addBreadcrumb(
                Mage::helper ('bot')->__('Queue Manager'),
                Mage::helper ('bot')->__('Queue Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('Bot'));
	    $this->_title ($this->__('Queue Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'queue.csv';

        $content = $this->getLayout()
            ->createBlock('bot/adminhtml_queue_grid')
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
                $model = Mage::getModel('bot/queue');

                $model->setId ($id)->delete ();
			}

			Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('bot')->__('Queue items were successfully removed.'));
		}
		catch (Exception $e)
        {
			Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());
		}

		$this->_redirect('*/*/');
	}
}

