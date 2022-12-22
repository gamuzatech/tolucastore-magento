<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Model_Observer
{
    const XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY = Toluca_PDV_Helper_Data::XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY;

    public function salesOrderPlaceAfter ($observer)
    {
        $event   = $observer->getEvent ();
        $order   = $event->getOrder ();
        $payment = $order->getPayment ();

        if (!$order->getIsPdv () || !$order->getPdvId () || !$order->getOperatorId ())
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

        $cashier = Mage::getModel ('pdv/cashier')->load ($order->getPdvId ());
        $operator = Mage::getModel ('pdv/operator')->load ($order->getOperatorId ());

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

