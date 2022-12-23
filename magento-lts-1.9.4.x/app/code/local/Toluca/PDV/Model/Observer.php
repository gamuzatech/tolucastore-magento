<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Model_Observer
{
    const API_METHOD_PDV_CASHIER_QUOTE = 'pdv_cashier.quote';

    const XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY = Toluca_PDV_Helper_Data::XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY;

    public function controllerActionPredispatch ($observer)
    {
        if (!strcmp (Mage::app ()->getRequest ()->getControllerModule (), 'Gamuza_JsonApi')
            && strpos (Mage::app ()->getRequest ()->getRawBody (), self::API_METHOD_PDV_CASHIER_QUOTE) !== false)
        {
            Mage::app ()->getStore ()->setConfig (
                Toluca_PDV_Helper_Data::XML_PATH_DEFAULT_EMAIL_PREFIX, 'pdv'
            );
        }
    }

    public function salesOrderPlaceAfter ($observer)
    {
        $event   = $observer->getEvent ();
        $order   = $event->getOrder ();
        $payment = $order->getPayment ();

        $orderIsPdv         = $order->getData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_IS_PDV);
        $orderPdvCashierId  = $order->getData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_CASHIER_ID);
        $orderPdvOperatorId = $order->getData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_OPERATOR_ID);

        if (!$orderIsPdv || !$orderPdvCashierId || !$orderPdvOperatorId)
        {
            return $this; // cancel
        }

        $amount = $order->getBaseGrandTotal ();

        $cashAmount   = floatval ($payment->getAdditionalInformation('cash_amount'));
        $changeAmount = floatval ($payment->getAdditionalInformation('change_amount'));
        $changeType   = intval ($payment->getAdditionalInformation('change_type'));

        if ($changeType == 1)
        {
            $amount = $cashAmount;
        }

        $cashier = Mage::getModel ('pdv/cashier')->load ($orderPdvCashierId);
        $operator = Mage::getModel ('pdv/operator')->load ($orderPdvOperatorId);

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_ORDER)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator->getId ())
            ->setOrderId ($order->getId ())
            ->setOrderIncrementId ($order->getIncrementId ())
            ->setPaymentMethod ($payment->getMethod ())
            ->setAmount ($amount)
            ->setMessage (
                $changeType == 1
                ? Mage::helper ('pdv')->__('Money Amount')
                : Mage::helper ('pdv')->__('Order Amount')
            )
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        $paymentMethod = Mage::getStoreConfig (self::XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY);

        if (!strcmp ($payment->getMethod (), $paymentMethod))
        {
            $history = Mage::getModel ('pdv/history')
                ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_ORDER)
                ->setCashierId ($cashier->getId ())
                ->setOperatorId ($operator->getId ())
                ->setOrderId ($order->getId ())
                ->setOrderIncrementId ($order->getIncrementId ())
                ->setPaymentMethod ($payment->getMethod ())
                ->setAmount (- $changeAmount)
                ->setMessage (Mage::helper ('pdv')->__('Change Amount'))
                ->setCreatedAt (date ('c'))
                ->save ()
            ;

            $cashier->setMoneyAmount (floatval ($cashier->getMoneyAmount ()) + $amount)
                ->setChangeAmount (floatval ($cashier->getChangeAmount ()) + $changeAmount)
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }
    }
}

