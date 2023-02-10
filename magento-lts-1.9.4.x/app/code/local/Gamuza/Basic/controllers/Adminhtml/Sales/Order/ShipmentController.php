<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2021 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

require_once (Mage::getModuleDir ('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'Order' . DS . 'ShipmentController.php');

/**
 * Adminhtml sales order shipment controller
 */
class Gamuza_Basic_Adminhtml_Sales_Order_ShipmentController
    extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return null
     */
    public function saveAction ()
    {
        parent::saveAction ();

        $orderId = $this->getRequest ()->getParam ('order_id');

        if ($shipment = Mage::registry ('current_shipment'))
        {
            $order = $shipment->getOrder ();

            $status  = Gamuza_Basic_Model_Sales_Order::STATUS_SHIPPED;
            $comment = Mage::helper ('basic')->__('The order was shipped.');

            $order->queueOrderUpdateEmail (true, $comment, true)
                ->addStatusHistoryComment ($comment, $status)
                ->setIsCustomerNotified (true)
                ->setIsVisibleOnFront (true)
                ->save ()
                ->getOrder ()
                ->save ()
            ;
        }

        $this->_redirect ('*/sales_order/view', array ('order_id' => $orderId));
    }
}

