<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Bot_Adminhtml_QueueController extends Mage_Adminhtml_Controller_Action
{
    use Gamuza_Bot_Trait_Queue;

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

    protected function _initQueue()
    {
        $id = $this->getRequest ()->getParam ('id');

        $queue = Mage::getModel ('bot/queue')->load ($id);

        if (!$queue || !$queue->getId ())
        {
            $this->_getSession ()->addError ($this->__('This queue no longer exists.'));

            $this->_redirect('*/*/index');

            $this->setFlag('', self::FLAG_NO_DISPATCH, true);

            return false;
        }

        $collection = Mage::getModel ('bot/message')->getCollection ()
            ->addFieldToFilter ('queue_id', array ('eq' => $queue->getId ()))
        ;

        if (!$collection->getSize ())
        {
            $this->_getSession ()->addError ($this->__('This queue has no history.'));

            $this->_redirect('*/*/index');

            $this->setFlag('', self::FLAG_NO_DISPATCH, true);

            return false;
        }

        Mage::register('bot_queue',     $queue);
        Mage::register('current_queue', $queue);

        return $queue;
    }

	public function indexAction ()
	{
	    $this->_title ($this->__('Bot'));
	    $this->_title ($this->__('Queue Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}

    public function historyAction ()
    {
        $queue = $this->_initQueue ();

        if ($queue && $queue->getId ())
        {
	        $this->_title ($this->__('Bot'));
	        $this->_title ($this->__('History Manager'));

		    $this->_initAction ();

		    $this->renderLayout ();
        }
    }
}

