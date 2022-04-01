<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2018 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

require_once (Mage::getModuleDir ('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'OrderController.php');

/**
 * Adminhtml sales orders controller
 */
class Gamuza_Basic_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array ('pending');

    public function pendingAction()
    {
        $collection = Mage::getModel ('sales/order')->getCollection ()
            ->addFieldToFilter ('state', array ('eq' => Mage_Sales_Model_Order::STATE_NEW))
        ;

        $collection->getSelect ()->reset (Zend_Db_Select::COLUMNS)
            ->columns (array ('qty' => 'COUNT(main_table.entity_id)'))
        ;

        $this->getResponse ()->setBody ($collection->getFirstItem ()->getQty ());
    }

    /**
     * Cancel order
     */
    public function cancelAction()
    {
        parent::cancelAction();

        if ($order = Mage::registry ('current_order'))
        {
            $status  = Gamuza_Basic_Model_Order::STATUS_CANCELED;
            $comment = Mage::helper ('basic')->__('The order was canceled.');

            $order->queueOrderUpdateEmail (true, $comment, true)
                ->addStatusHistoryComment ($comment, $status)
                ->setIsCustomerNotified (true)
                ->setIsVisibleOnFront (true)
                ->save ()
                ->getOrder ()
                ->save ()
            ;
        }

        $this->_redirect ('*/sales_order/view', array ('order_id' => $order->getId ()));
    }

    /**
     * Prepare order to delivery
     */
    public function prepareAction()
    {
        if ($order = $this->_initOrder ())
        {
            try
            {
                $status  = Gamuza_Basic_Model_Order::STATUS_PREPARING;
                $comment = Mage::helper ('basic')->__('The order is being prepared.');

                $order->queueOrderUpdateEmail (true, $comment, true)
                    ->addStatusHistoryComment ($comment, $status)
                    ->setIsCustomerNotified (true)
                    ->setIsVisibleOnFront (true)
                    ->save ()
                    ->getOrder ()
                    ->save ()
                ;

                $this->_getSession()->addSuccess ($this->__('The order notification has been sent.'));
            }
            catch (Mage_Core_Exception $e)
            {
                $this->_getSession ()->addError ($e->getMessage ());
            }
            catch (Exception $e)
            {
                $this->_getSession ()->addError ($this->__('Failed to send the order notification.'));

                // Mage::logException ($e);
            }
        }

        $this->_redirect ('*/sales_order/view', array ('order_id' => $order->getId ()));
    }

    /**
     * Delivered order status
     */
    public function deliveredStatusAction()
    {
        if ($order = $this->_initOrder ())
        {
            try
            {
                $status  = Gamuza_Basic_Model_Order::STATUS_DELIVERED;
                $comment = $this->__('The order was delivered.');

                $order->queueOrderUpdateEmail (true, $comment, true)
                    ->addStatusHistoryComment ($comment, $status)
                    ->setIsCustomerNotified (true)
                    ->setIsVisibleOnFront (true)
                    ->save ()
                    ->getOrder ()
                    ->save ()
                ;

                $this->_getSession()->addSuccess ($this->__('The order notification has been sent.'));
            }
            catch (Mage_Core_Exception $e)
            {
                $this->_getSession ()->addError ($e->getMessage ());
            }
            catch (Exception $e)
            {
                $this->_getSession ()->addError ($this->__('Failed to send the order notification.'));

                // Mage::logException ($e);
            }
        }

        $this->_redirect ('*/sales_order/view', array ('order_id' => $order->getId ()));
    }
}

