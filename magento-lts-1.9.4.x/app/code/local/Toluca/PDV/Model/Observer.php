<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Model_Observer
{
    const XML_PATH_PDV_SETTING_DEFAULT_CASHIER  = Toluca_PDV_Helper_Data::XML_PATH_PDV_SETTING_DEFAULT_CASHIER;
    const XML_PATH_PDV_SETTING_DEFAULT_OPERATOR = Toluca_PDV_Helper_Data::XML_PATH_PDV_SETTING_DEFAULT_OPERATOR;
    const XML_PATH_PDV_SETTING_DEFAULT_CUSTOMER = Toluca_PDV_Helper_Data::XML_PATH_PDV_SETTING_DEFAULT_CUSTOMER;

    const XML_PATH_PDV_CASHIER_INCLUDE_ALL_ORDERS = Toluca_PDV_Helper_Data::XML_PATH_PDV_CASHIER_INCLUDE_ALL_ORDERS;

    const XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY = Toluca_PDV_Helper_Data::XML_PATH_PDV_PAYMENT_METHOD_CASHONDELIVERY;

    const XML_PATH_PDV_PAYMENT_METHOD_ALL = Toluca_PDV_Helper_Data::XML_PATH_PDV_PAYMENT_METHOD_ALL;

    public function controllerActionPredispatch ($observer)
    {
        $controllerModule = Mage::app ()->getRequest ()->getControllerModule ();

        $isPdv = Mage::helper ('pdv')->isPDV ();

        if (!strcmp ($controllerModule, 'Toluca_PDV') || $isPdv)
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

        $orderPdvCashierId  = Mage::getStoreConfig (self::XML_PATH_PDV_SETTING_DEFAULT_CASHIER);
        $orderPdvOperatorId = Mage::getStoreConfig (self::XML_PATH_PDV_SETTING_DEFAULT_OPERATOR);
        $orderPdvCustomerId = Mage::getStoreConfig (self::XML_PATH_PDV_SETTING_DEFAULT_CUSTOMER);

        $orderIsPdv = boolval ($order->getData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_IS_PDV));

        if ($orderIsPdv)
        {
            $orderPdvCashierId  = $order->getData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_CASHIER_ID);
            $orderPdvOperatorId = $order->getData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_OPERATOR_ID);
            $orderPdvCustomerId = $order->getData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_CUSTOMER_ID);
        }

        if (!$orderIsPdv && !Mage::getStoreConfigFlag (self::XML_PATH_PDV_CASHIER_INCLUDE_ALL_ORDERS))
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
        $customer = Mage::getModel ('customer/customer')->load ($orderPdvCustomerId);

        $history = Mage::getModel ('pdv/history')->load ($cashier->getHistoryId ());

        $history->setShippingAmount (floatval ($history->getShippingAmount ()) + $order->getBaseShippingAmount ());

        $log = Mage::getModel ('pdv/log')
            ->setTypeId (Toluca_PDV_Helper_Data::LOG_TYPE_ORDER)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator->getId ())
            ->setHistoryId ($history->getId ())
            ->setCustomerId ($customer->getId ())
            ->setOrderId ($order->getId ())
            ->setOrderIncrementId ($order->getIncrementId ())
            ->setShippingMethod ($order->getShippingMethod ())
            ->setPaymentMethod ($payment->getMethod ())
            ->setShippingAmount ($order->getBaseShippingAmount ())
            ->setTotalAmount ($amount)
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
            $log = Mage::getModel ('pdv/log')
                ->setTypeId (Toluca_PDV_Helper_Data::LOG_TYPE_ORDER)
                ->setCashierId ($cashier->getId ())
                ->setOperatorId ($operator->getId ())
                ->setHistoryId ($history->getId ())
                ->setCustomerId ($customer->getId ())
                ->setOrderId ($order->getId ())
                ->setOrderIncrementId ($order->getIncrementId ())
                ->setShippingMethod ($order->getShippingMethod ())
                ->setPaymentMethod ($payment->getMethod ())
                ->setShippingAmount ($order->getBaseShippingAmount ())
                ->setTotalAmount (- $changeAmount)
                ->setMessage (Mage::helper ('pdv')->__('Change Amount'))
                ->setCreatedAt (date ('c'))
                ->save ()
            ;

            $history->setMoneyAmount (floatval ($history->getMoneyAmount ()) + $amount)
                ->setChangeAmount (floatval ($history->getChangeAmount ()) + $changeAmount)
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }
        else
        {
            $paymentAllMethods = Mage::getStoreConfig (self::XML_PATH_PDV_PAYMENT_METHOD_ALL);

            foreach ($paymentAllMethods as $code => $value)
            {
                if (!strcmp ($payment->getMethod (), $value))
                {
                    $fieldAmount = sprintf ('%s_amount', $code);

                    $historyAmount = floatval ($history->getData ($fieldAmount));

                    $history->setData ($fieldAmount, $historyAmount + $amount)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }
            }
        }
    }
}

