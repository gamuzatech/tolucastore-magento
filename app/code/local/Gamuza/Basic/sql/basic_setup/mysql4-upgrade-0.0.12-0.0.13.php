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

$installer = new Mage_Core_Model_Resource_Setup ('basic_setup');
$installer->startSetup ();

Mage::app ()->getLocale ()->setLocale (Mage::helper ('basic')->getLocaleCode ());

Mage::app ()->getTranslator ()->init (Mage_Core_Model_App_Area::AREA_ADMINHTML, true);

$orderStatus = Mage::getModel ('sales/order_status')
    ->setStatus (Gamuza_Basic_Helper_Data::ORDER_STATUS_PREPARING)
    ->setLabel (Mage::helper ('basic')->__('Preparing'))
    ->save ()
;

$orderStatus->assignState (Mage_Sales_Model_Order::STATE_NEW);

$orderStatus = Mage::getModel ('sales/order_status')
    ->setStatus (Gamuza_Basic_Helper_Data::ORDER_STATUS_PAID)
    ->setLabel (Mage::helper ('basic')->__('Paid'))
    ->save ()
;

$orderStatus->assignState (Mage_Sales_Model_Order::STATE_PROCESSING);
$orderStatus->assignState (Mage_Sales_Model_Order::STATE_COMPLETE);

$orderStatus = Mage::getModel ('sales/order_status')
    ->setStatus (Gamuza_Basic_Helper_Data::ORDER_STATUS_SHIPPED)
    ->setLabel (Mage::helper ('basic')->__('Shipped'))
    ->save ()
;

$orderStatus->assignState (Mage_Sales_Model_Order::STATE_PROCESSING);
$orderStatus->assignState (Mage_Sales_Model_Order::STATE_COMPLETE);

$orderStatus = Mage::getModel ('sales/order_status')
    ->setStatus (Gamuza_Basic_Helper_Data::ORDER_STATUS_DELIVERED)
    ->setLabel (Mage::helper ('basic')->__('Delivered'))
    ->save ()
;

$orderStatus->assignState (Mage_Sales_Model_Order::STATE_COMPLETE);

$orderStatus = Mage::getModel ('sales/order_status')
    ->setStatus (Gamuza_Basic_Helper_Data::ORDER_STATUS_REFUNDED)
    ->setLabel (Mage::helper ('basic')->__('Refunded'))
    ->save ()
;

$orderStatus->assignState (Mage_Sales_Model_Order::STATE_CLOSED);

$installer->endSetup ();

