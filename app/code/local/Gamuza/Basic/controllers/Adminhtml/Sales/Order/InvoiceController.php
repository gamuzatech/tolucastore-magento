<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2021 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

/**
 * See the AUTHORS file for a list of people on the Gamuza Team.
 * See the ChangeLog files for a list of changes.
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

require_once (Mage::getModuleDir ('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'Order' . DS . 'InvoiceController.php');

/**
 * Adminhtml sales order edit controller
 */
class Gamuza_Basic_Adminhtml_Sales_Order_InvoiceController
    extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    /**
     * Save invoice
     * We can save only new invoice. Existing invoices are not editable
     */
    public function saveAction ()
    {
        parent::saveAction ();

        $orderId = $this->getRequest ()->getParam ('order_id');

        if ($invoice = Mage::registry ('current_invoice'))
        {
            $order = $invoice->getOrder ();

            $status  = Gamuza_Basic_Model_Order::STATUS_PAID;
            $comment = Mage::helper ('basic')->__('The order was paid.');

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

