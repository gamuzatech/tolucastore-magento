<?php
/**
 * @package     Gamuza_Mobile
 * @copyright   Copyright (c) 2017 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * These files are distributed with gamuza_mobile-magento at http://github.com/gamuzatech/.
 */

class Gamuza_Mobile_Model_Cart_Api extends Mage_Checkout_Model_Api_Resource
{
    use Gamuza_Mobile_Trait_Api_Resource;

    protected $_quoteAmountAttributes = array(
        'items_count', 'items_qty',
        'grand_total', 'base_grand_total',
        'subtotal', 'base_subtotal',
        'subtotal_with_discount', 'base_subtotal_with_discount',
    );

    protected $_intAttributes = array(
        /* info */
        'items_count', 'customer_gender',
        /* address */
        'region_id'
    );

    protected $_floatAttributes = array(
        /* info */
        'items_qty', 'grand_total', 'base_grand_total', 'subtotal', 'base_subtotal', 'subtotal_with_discount', 'base_subtotal_with_discount',
        /* address */
        'weight', 'shipping_amount', 'base_shipping_amount', 'discount_amount', 'base_discount_amount', 'shipping_discount_amount', 'base_shipping_discount_amount',
        /* item */
        'price', 'base_price', 'custom_price', 'discount_percent', 'row_total', 'base_row_total', 'row_total_with_discount', 'row_weight'
    );

    /**
     * Retrieve amount information about quote
     *
     * @param  $quoteId
     * @param  $store
     * @return array
     */
    public function amount(/* $quoteId, */ $store = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        $result = $this->_getAttributes($quote, 'quote', $this->_quoteAmountAttributes);

        foreach ($this->_intAttributes as $code)
        {
            if (array_key_exists ($code, $result))
            {
                $result [$code] = intval ($result [$code]);
            }
        }

        foreach ($this->_floatAttributes as $code)
        {
            if (array_key_exists ($code, $result))
            {
                $result [$code] = floatval ($result [$code]);
            }
        }

        return $result;
    }

    /**
     * @param  $quoteId
     * @param  $store
     * @return void
     */
    public function totals($store = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        $totals = $quote->getTotals();

        $totalsResult = array();

        foreach ($totals as $total)
        {
            $totalsResult[] = array(
                "title" => $total->getTitle(),
                "amount" => floatval($total->getValue())
            );
        }

        return $totalsResult;
    }

    /**
     * Create an order from the shopping cart (quote)
     *
     * @param  $quoteId
     * @param  $store
     * @param  $agreements array
     * @return string
     */
    public function createOrder($store = null, $agreements = null)
    {
        $incrementId = $this->_createOrder ($store, $agreements);

        $result = array(
            'increment_id' => $incrementId,
            'blockchain'   => null,
            'picpay'       => null,
        );

        if (Mage::helper ('core')->isModuleEnabled ('Gamuza_Blockchain'))
        {
            $transaction = Mage::getModel ('blockchain/transaction')->load ($incrementId, 'order_increment_id');

            if ($transaction && $transaction->getId ())
            {
                $result ['blockchain'] = array (
                    'type'    => $transaction->getCurrencyType (),
                    'address' => $transaction->getAddress (),
                    'amount'  => $transaction->getAmount (),
                );
            }
        }

        if (Mage::helper ('core')->isModuleEnabled ('Gamuza_PicPay'))
        {
            $transaction = Mage::getModel ('picpay/transaction')->load ($incrementId, 'order_increment_id');

            if ($transaction && $transaction->getId ())
            {
                $result ['picpay'] = array (
                    'status' => $transaction->getStatus (),
                    'url'    => $transaction->getPaymentUrl (),
                );
            }
        }

        return $result;
    }

    /**
     * Create an order from the shopping cart (quote)
     *
     * @param  $quoteId
     * @param  $store
     * @param  $agreements array
     * @return string
     */
    protected function _createOrder($store = null, $agreements = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();

        if (!empty($requiredAgreements))
        {
            if (empty ($agreements) || !is_array($agreements))
            {
                $this->_fault ('required_agreements_are_not_specified');
            }

            $diff = array_diff($agreements, $requiredAgreements);

            if (!empty($diff))
            {
                $this->_fault('required_agreements_are_not_all');
            }
        }

        $quote = $this->_getCustomerQuote($store);

        if ($quote->getIsMultiShipping())
        {
            $this->_fault('invalid_checkout_type');
        }

        if ($quote->getCheckoutMethod() == Mage_Checkout_Model_Api_Resource_Customer::MODE_GUEST
                && !Mage::helper('checkout')->isAllowedGuestCheckout($quote, $quote->getStoreId())
        )
        {
            $this->_fault('guest_checkout_is_not_enabled');
        }

        /** @var $customerResource Mage_Checkout_Model_Api_Resource_Customer */
        $customerResource = Mage::getModel("checkout/api_resource_customer");

        $isNewCustomer = $customerResource->prepareCustomerForQuote($quote);

        /*
         * Validate: is_in_stock
         */
        foreach ($quote->getAllItems () as $item)
        {
            $stockItem = Mage::getModel ('catalogInventory/stock_item')->loadByProduct ($item->getProductId ());

            if (!$stockItem || !$stockItem->getId () || !$stockItem->getIsInStock ())
            {
                $this->_fault ('create_order_fault', Mage::helper ('mobile')->__('This product is currently out of stock: %s', $item->getProduct ()->getName ()));
            }
        }

        try
        {
            Mage::getSingleton('checkout/session')->setData('PsPayment', serialize($quote->getPayment()->getAdditionalInformation())); // pagseguro_cc

            $quote->collectTotals();

            /** @var $service Mage_Sales_Model_Service_Quote */
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();

            if ($isNewCustomer)
            {
                try
                {
                    $customerResource->involveNewCustomer($quote);
                }
                catch (Exception $e)
                {
                    Mage::logException($e);
                }
            }

            $order = $service->getOrder();

            if ($order)
            {
                Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                    array('order' => $order, 'quote' => $quote)
                );

                try
                {
                    $order->queueNewOrderEmail();
                }
                catch (Exception $e)
                {
                    Mage::logException($e);
                }
            }

            Mage::dispatchEvent('checkout_submit_all_after',
                array('order' => $order, 'quote' => $quote)
            );

            $quote->delete (); // Discard
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_fault('create_order_fault', $e->getMessage());
        }

        return $order->getIncrementId();
    }

    /**
     * @param  $store
     * @return bool
     */
    public function clear ($store = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote ($store);

        $quote->delete ();

        return true;
    }
}

