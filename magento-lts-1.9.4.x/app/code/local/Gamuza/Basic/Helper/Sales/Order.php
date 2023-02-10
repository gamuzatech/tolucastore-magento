<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2023 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Basic_Helper_Sales_Order extends Mage_Core_Helper_Abstract
{
    public function cancel ($order)
    {
        $status  = Gamuza_Basic_Model_Sales_Order::STATUS_CANCELED;
        $comment = Mage::helper ('basic')->__('The order was canceled.');

        $order->queueOrderUpdateEmail (true, $comment, true)
            ->addStatusHistoryComment ($comment, $status)
            ->setIsCustomerNotified (true)
            ->setIsVisibleOnFront (true)
            ->save ()
            ->getOrder ()
            ->save ()
        ;

        Mage::dispatchEvent ('sales_order_cancel_after', array ('order' => $order));
    }

    public function prepare ($order)
    {
        $status  = Gamuza_Basic_Model_Sales_Order::STATUS_PREPARING;
        $comment = Mage::helper ('basic')->__('The order is being prepared.');

        $order->queueOrderUpdateEmail (true, $comment, true)
            ->addStatusHistoryComment ($comment, $status)
            ->setIsCustomerNotified (true)
            ->setIsVisibleOnFront (true)
            ->save ()
            ->getOrder ()
            ->save ()
        ;

        Mage::dispatchEvent ('sales_order_prepare_after', array ('order' => $order));
    }

    public function delivered ($order)
    {
        $status  = Gamuza_Basic_Model_Sales_Order::STATUS_DELIVERED;
        $comment = $this->__('The order was delivered.');

        $order->queueOrderUpdateEmail (true, $comment, true)
            ->addStatusHistoryComment ($comment, $status)
            ->setIsCustomerNotified (true)
            ->setIsVisibleOnFront (true)
            ->save ()
            ->getOrder ()
            ->save ()
        ;

        Mage::dispatchEvent ('sales_order_delivered_after', array ('order' => $order));
    }
}

