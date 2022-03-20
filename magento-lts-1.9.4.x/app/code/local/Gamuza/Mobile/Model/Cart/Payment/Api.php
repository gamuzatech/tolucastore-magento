<?php
/**
 * @package     Gamuza_Mobile
 * @copyright   Copyright (c) 2017 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Shopping cart api
 */
class Gamuza_Mobile_Model_Cart_Payment_Api extends Mage_Checkout_Model_Cart_Payment_Api
{
    use Gamuza_Mobile_Trait_Api_Resource;

    const DISTRO_STORE_ID = Mage_Core_Model_App::DISTRO_STORE_ID;

    /**
     * Retrieve available payment methods for a quote
     *
     * @param int $quoteId
     * @param int $store
     * @return array
     */
    public function _getPaymentMethodsList($store = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        $total = $quote->getBaseSubtotal();

        $methodsResult = array();
        $methods = Mage::helper('payment')->getStoreMethods(self::DISTRO_STORE_ID, $quote);

        foreach ($methods as $method)
        {
            /** @var $method Mage_Payment_Model_Method_Abstract */
            if ($this->_canUsePaymentMethod($method, $quote))
            {
                $isRecurring = $quote->hasRecurringItems() && $method->canManageRecurringProfiles();

                if ($total != 0 || $method->getCode() == 'free' || $isRecurring)
                {
                    $methodsResult[] = array(
                        'code' => $method->getCode(),
                        'title' => $method->getTitle(),
                        'cc_types' => $this->_getPaymentMethodAvailableCcTypes($method),
                        'base_grand_total' => floatval ($quote->getBaseGrandTotal ()),
                        /* creditcard */
                        'installments' => $this->_getCcAvailableInstallments ($quote, $method),
                        /* checkmo */
                        'mailing_address' => $method->getConfigData('mailing_address'),
                        /* banktransfer */
                        'instructions' => $method->getConfigData('instructions')
                    );
                }
            }
        }

        return $methodsResult;
    }

    /**
     * @param  $quoteId
     * @param  $paymentData
     * @param  $store
     * @return bool
     */
    public function _setPaymentMethod($store = null, $paymentData = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        $paymentData = $this->_preparePaymentData($paymentData);

        if (empty($paymentData))
        {
            $this->_fault("payment_method_empty");
        }

        if ($quote->isVirtual())
        {
            // check if billing address is set
            if (is_null($quote->getBillingAddress()->getId()))
            {
                $this->_fault('billing_address_is_not_set');
            }

            $quote->getBillingAddress()->setPaymentMethod(
                isset($paymentData['method']) ? $paymentData['method'] : null
            );
        }
        else
        {
            // check if shipping address is set
            if (is_null($quote->getShippingAddress()->getId()))
            {
                $this->_fault('shipping_address_is_not_set');
            }

            $quote->getShippingAddress()->setPaymentMethod(
                isset($paymentData['method']) ? $paymentData['method'] : null
            );
        }

        if (!$quote->isVirtual() && $quote->getShippingAddress())
        {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $total = $quote->getBaseSubtotal();

        $methods = Mage::helper('payment')->getStoreMethods(self::DISTRO_STORE_ID, $quote);

        foreach ($methods as $method)
        {
            if ($method->getCode() == $paymentData['method'])
            {
                /** @var $method Mage_Payment_Model_Method_Abstract */
                if (!($this->_canUsePaymentMethod($method, $quote)
                    && ($total != 0
                        || $method->getCode() == 'free'
                        || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles())))
                )
                {
                    $this->_fault("method_not_allowed");
                }
            }
        }

        try
        {
            $payment = $quote->getPayment();
            $payment->importData($paymentData);

            $quote->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_fault('payment_method_is_not_set', $e->getMessage());
        }

        return true;
    }
}

