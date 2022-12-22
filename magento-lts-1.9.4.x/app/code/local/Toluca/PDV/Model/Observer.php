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

        if (!$order->getIsPdv () || !$order->getPdvId () || !$order->getPdvUserId ())
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

        $item = Mage::getModel ('pdv/item')->load ($order->getPdvId ());
        $user = Mage::getModel ('pdv/user')->load ($order->getPdvUserId ());

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_ORDER)
            ->setItemId ($item->getId ())
            ->setUserId ($user->getId ())
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
                ->setItemId ($item->getId ())
                ->setUserId ($user->getId ())
                ->setOrderId ($order->getId ())
                ->setOrderIncrementId ($order->getIncrementId ())
                ->setPaymentMethod ($payment->getMethod ())
                ->setAmount (- $changeAmount)
                ->setMessage (Mage::helper ('pdv')->__('Change Amount'))
                ->setCreatedAt (date ('c'))
                ->save ()
            ;

            $item->setMoneyAmount (floatval ($item->getMoneyAmount ()) + $amount)
                ->setChangeAmount (floatval ($item->getChangeAmount ()) + $changeAmount)
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }
    }
}

