<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Adminhtml sales order view
 */
class Gamuza_Basic_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('send_notification');
        $this->_removeButton('order_reorder');
        $this->_removeButton('order_edit');
        $this->_removeButton('order_hold');
        $this->_removeButton('order_unhold');

        $order = $this->getOrder ();

        /**
         * Prepare
         */
        if (!strcmp ($order->getState (), Mage_Sales_Model_Order::STATE_NEW) && !strcmp ($order->getStatus (), Gamuza_Basic_Model_Order::STATUS_PENDING))
        {
            $coreHelper = Mage::helper ('core');

            $confirmationMessage = $coreHelper->jsQuoteEscape(
                Mage::helper ('basic')->__('Are you sure?')
            );

            $onclickJs = sprintf ("confirmSetLocation ('%s', '%s');", $confirmationMessage, $this->getPrepareUrl ());

            $this->addButton ('order_prepare', array(
                'label'   => Mage::helper ('basic')->__('Prepare'),
                'class'   => 'scalable go',
                'onclick' => $onclickJs,
            ), 1);

            $this->removeButton ('order_invoice');
            $this->removeButton ('order_ship');
        }

        /**
         * Delivered
         */
        if (!strcmp ($order->getState (), Mage_Sales_Model_Order::STATE_COMPLETE)
            && in_array ($order->getStatus (), array(
                Gamuza_Basic_Model_Order::STATUS_PAID, Gamuza_Basic_Model_Order::STATUS_SHIPPED
            )))
        {
            $coreHelper = Mage::helper ('core');

            $confirmationMessage = $coreHelper->jsQuoteEscape(
                Mage::helper ('basic')->__('Are you sure?')
            );

            $onclickJs = sprintf ("confirmSetLocation ('%s', '%s');", $confirmationMessage, $this->getDeliveredStatusUrl ());

            $this->addButton ('order_delivered', array(
                'label'   => Mage::helper ('basic')->__('Delivered'),
                'class'   => 'scalable go',
                'onclick' => $onclickJs,
            ), 1);
        }
    }

    public function getPrepareUrl ()
    {
        return $this->getUrl ('*/*/prepare');
    }

    public function getDeliveredStatusUrl ()
    {
        return $this->getUrl ('*/*/deliveredStatus');
    }
}

