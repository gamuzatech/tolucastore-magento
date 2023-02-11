<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2023 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Order API
 */
class Gamuza_Basic_Model_Order_Api extends Mage_Api_Model_Resource_Abstract
{
    public function cancel ($incrementId, $protectCode, $comment = null)
    {
        $order = $this->_getOrder ($incrementId, $protectCode);

        if (!strcmp ($order->getStatus (), Gamuza_Basic_Model_Sales_Order::STATUS_CANCELED))
        {
            $this->_fault ('order_has_canceled');
        }

        if (!$order->canCancel ())
        {
            $this->_fault ('order_not_canceled');
        }

        $order->cancel ($comment)->save ();

        Mage::helper ('basic/sales_order')->cancel ($order);

        return true;
    }

    public function prepare ($incrementId, $protectCode)
    {
        $order = $this->_getOrder ($incrementId, $protectCode);

        if (!strcmp ($order->getStatus (), Gamuza_Basic_Model_Sales_Order::STATUS_PREPARING))
        {
            $this->_fault ('order_has_prepared');
        }

        if (!$order->canPrepare ())
        {
            $this->_fault ('order_not_prepared');
        }

        Mage::helper ('basic/sales_order')->prepare ($order);

        return true;
    }

    public function delivered ($incrementId, $protectCode)
    {
        $order = $this->_getOrder ($incrementId, $protectCode);

        if (!strcmp ($order->getStatus (), Gamuza_Basic_Model_Sales_Order::STATUS_DELIVERED))
        {
            $this->_fault ('order_has_delivered');
        }

        if (!$order->canDeliver ())
        {
            $this->_fault ('order_not_delivered');
        }

        Mage::helper ('basic/sales_order')->delivered ($order);

        return true;
    }

    protected function _getOrder ($incrementId, $protectCode)
    {
        if (empty ($incrementId) || empty ($protectCode))
        {
            $this->_fault ('order_not_specified');
        }

        $order = Mage::getModel ('sales/order')->getCollection ()
            ->addFieldToFilter ('increment_id', array ('eq' => $incrementId))
            ->addFieldToFilter ('protect_code', array ('eq' => $protectCode))
            ->getFirstItem ()
        ;

        if (!$order || !$order->getId ())
        {
            $this->_fault ('order_not_exists');
        }

        return $order;        
    }
}

