<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
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

