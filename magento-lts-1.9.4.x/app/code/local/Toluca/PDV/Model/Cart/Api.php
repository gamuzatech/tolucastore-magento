<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Cart API
 */
class Toluca_PDV_Model_Cart_Api extends Mage_Api_Model_Resource_Abstract
{
    public function items ($cashier_id, $operator_id, $customer_id = 0)
    {
        if (empty ($cashier_id))
        {
            $this->_fault ('cashier_not_specified');
        }

        if (empty ($operator_id))
        {
            $this->_fault ('operator_not_specified');
        }

        $cashier = Mage::getModel ('pdv/cashier')->load ($cashier_id);

        if (!$cashier || !$cashier->getId ())
        {
            $this->_fault ('cashier_not_exists');
        }

        $operator = Mage::getModel ('pdv/operator')->load ($operator_id);

        if (!$operator || !$operator->getId ())
        {
            $this->_fault ('operator_not_exists');
        }

        if (!empty ($customer_id))
        {
            $customer = Mage::getModel ('customer/customer')->load ($customer_id);

            if (!$customer || !$customer->getId ())
            {
                $this->_fault ('customer_not_exists');
            }
        }

        $result = array ();

        $collection = Mage::getModel ('sales/quote')->getCollection ()
            ->addFieldToFilter ('is_pdv',          array ('eq' => true))
            ->addFieldToFilter ('pdv_cashier_id',  array ('eq' => $cashier->getId ()))
            ->addFieldToFilter ('pdv_operator_id', array ('eq' => $operator->getId ()))
        ;

        if (!empty ($customer_id))
        {
            $collection->addFieldToFilter ('pdv_customer_id', array ('eq' => $customer_id));
        }

        foreach ($collection as $quote)
        {
            $result [] = array(
                'entity_id' => intval ($quote->getId ()),
                'store_id'  => intval ($quote->getStoreId ()),
                'created_at' => $quote->getCreatedAt (),
                'updated_at' => $quote->getUpdatedAt (),
                'is_active'  => boolval ($quote->getIsActive ()),
                'is_virtual' => boolval ($quote->getIsVirtual ()),
                'items_count' => intval ($quote->getItemsCount ()),
                'items_qty'   => intval ($quote->getItemsQty ()),
                'base_currency_code' => $quote->getBaseCurrencyCode (),
                'base_grand_total' => floatval ($quote->getBaseGrandTotal ()),
                'checkout_method' => $quote->getCheckoutMethod (),
                'customer_group_id'  => intval ($quote->getCustomerGroupId ()),
                'customer_email'     => $quote->getCustomerEmail (),
                'customer_firstname' => $quote->getCustomerFirstname (),
                'customer_lastname'  => $quote->getCustomerLastname (),
                'customer_note' => $quote->getCustomerNote (),
                'customer_is_guest'  => boolval ($quote->getCustomerIsGuest ()),
                'remote_ip' => $quote->getRemoteIp (),
                'customer_taxvat' => $quote->getCustomerTaxvat (),
                'base_subtotal' => floatval ($quote->getBaseSubtotal ()),
                'base_subtotal_with_discount' => floatval ($quote->getBaseSubtotalWithDiscount ()),
                'is_changed' => boolval ($quote->getIsChanged ()),
                'is_pdv' => boolval ($quote->getIsPdv ()),
                'pdv_cashier_id'  => intval ($quote->getPdvCashierId ()),
                'pdv_operator_id' => intval ($quote->getPdvOperatorId ()),
                'pdv_customer_id' => intval ($quote->getPdvCustomerId ()),
                'pdv_history_id'  => intval ($quote->getPdvHistoryId ()),
                'store_info_code'    => $quote->getStoreInfoCode (),
                'customer_info_code' => $quote->getCustomerInfoCode (),
            );
        }

        return $result;
    }

    public function info ($quote_id)
    {
        if (empty ($quote_id))
        {
            $this->_fault ('quote_not_specified');
        }

        $quote = Mage::getModel ('sales/quote')
            ->setStoreId (Mage_Core_Model_App::DISTRO_STORE_ID)
            ->load ($quote_id)
        ;

        if (!$quote || !$quote->getId ())
        {
            $this->_fault ('quote_not_exists');
        }

        $result = array(
            'entity_id' => intval ($quote->getId ()),
            'store_id'  => intval ($quote->getStoreId ()),
            'created_at' => $quote->getCreatedAt (),
            'updated_at' => $quote->getUpdatedAt (),
            'is_active'  => boolval ($quote->getIsActive ()),
            'is_virtual' => boolval ($quote->getIsVirtual ()),
            'items_count' => intval ($quote->getItemsCount ()),
            'items_qty'   => intval ($quote->getItemsQty ()),
            'base_currency_code' => $quote->getBaseCurrencyCode (),
            'base_grand_total' => floatval ($quote->getBaseGrandTotal ()),
            'checkout_method' => $quote->getCheckoutMethod (),
            'customer_group_id'  => intval ($quote->getCustomerGroupId ()),
            'customer_email'     => $quote->getCustomerEmail (),
            'customer_firstname' => $quote->getCustomerFirstname (),
            'customer_lastname'  => $quote->getCustomerLastname (),
            'customer_note' => $quote->getCustomerNote (),
            'customer_is_guest'  => boolval ($quote->getCustomerIsGuest ()),
            'remote_ip' => $quote->getRemoteIp (),
            'customer_taxvat' => $quote->getCustomerTaxvat (),
            'base_subtotal' => floatval ($quote->getBaseSubtotal ()),
            'base_subtotal_with_discount' => floatval ($quote->getBaseSubtotalWithDiscount ()),
            'is_changed' => boolval ($quote->getIsChanged ()),
            'is_pdv' => boolval ($quote->getIsPdv ()),
            'pdv_cashier_id'  => intval ($quote->getPdvCashierId ()),
            'pdv_operator_id' => intval ($quote->getPdvOperatorId ()),
            'pdv_customer_id' => intval ($quote->getPdvCustomerId ()),
            'pdv_history_id'  => intval ($quote->getPdvHistoryId ()),
            'store_info_code'    => $quote->getStoreInfoCode (),
            'customer_info_code' => $quote->getCustomerInfoCode (),
        );

        return $result;
    }

    public function create ($cashier_id, $operator_id, $customer_id, $quote_id = 0, $message = null)
    {
        $quote = $this->_getQuote ($cashier_id, $operator_id, $customer_id, $quote_id, $message);

        return intval ($quote->getId ());
    }

    protected function _getQuote ($cashier_id, $operator_id, $customer_id, $quote_id, $message)
    {
        if (empty ($cashier_id))
        {
            $this->_fault ('cashier_not_specified');
        }

        if (empty ($operator_id))
        {
            $this->_fault ('operator_not_specified');
        }

        if (empty ($customer_id))
        {
            $this->_fault ('customer_not_specified');
        }

        $cashier = Mage::getModel ('pdv/cashier')->load ($cashier_id);

        if (!$cashier || !$cashier->getId ())
        {
            $this->_fault ('cashier_not_exists');
        }

        $operator = Mage::getModel ('pdv/operator')->load ($operator_id);

        if (!$operator || !$operator->getId ())
        {
            $this->_fault ('operator_not_exists');
        }

        $customer = Mage::getModel ('customer/customer')->load ($customer_id);

        if (!$customer || !$customer->getId ())
        {
            $this->_fault ('customer_not_exists');
        }

        $customerBillingAddress  = $customer->getDefaultBillingAddress ();
        $customerShippingAddress = $customer->getDefaultShippingAddress ();

        if (!$customerBillingAddress || !$customerBillingAddress->getId ())
        {
            $this->_fault ('customer_billing_address_not_exists');
        }

        $customerBillingValidate = $customerBillingAddress->validate ();

        if ($customerBillingValidate !== true)
        {
            $this->_fault ('customer_invalid_billing_address', implode("\n", $customerBillingValidate));
        }

        if (!$customerShippingAddress || !$customerShippingAddress->getId ())
        {
            $this->_fault ('customer_shipping_address_not_exists');
        }

        $customerShippingValidate = $customerShippingAddress->validate ();

        if ($customerShippingValidate !== true)
        {
            $this->_fault ('customer_invalid_shipping_address', implode("\n", $customerShippingValidate));
        }

        $history = Mage::getModel ('pdv/history')->load ($cashier->getHistoryId ());

        if (!$history || !$history->getId ())
        {
            $this->_fault ('history_not_exists');
        }

        $collection = Mage::getModel ('sales/quote')->getCollection ()
            ->addFieldToFilter ('pdv_cashier_id',  array ('eq' => $cashier_id))
            ->addFieldToFilter ('pdv_operator_id', array ('eq' => $operator_id))
            ->addFieldToFilter ('pdv_customer_id', array ('eq' => $customer_id))
            ->addFieldToFilter ('entity_id',       array ('eq' => $quote_id))
        ;

        $quote = $collection->getFirstItem ();

        if ($quote && $quote->getId ())
        {
            $quote->afterLoad ();

            goto __returnQuote;
        }

        $storeId = Mage_Core_Model_App::DISTRO_STORE_ID;

        $remoteIp = Mage::helper ('core/http')->getRemoteAddr (false);

        /**
         * NOTE: uniq_id instead customer_id
         */
        $customerEmail = Mage::helper ('pdv')->getCustomerEmail (uniqid('c_', true));

        $quote = Mage::getModel ('sales/quote')
            ->setStoreId ($storeId)
            ->setIsActive (true)
            ->setIsMultiShipping (false)
            ->setRemoteIp ($remoteIp)
            ->setCustomerFirstname ($customer->getFirstname ())
            ->setCustomerLastname ($customer->getLastname ())
            ->setCustomerEmail ($customerEmail)
            ->setCustomerTaxvat ($customer->getTaxvat ())
            ->setCustomerNote ($message)
        ;

        $quote->setData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_IS_PDV, true)
            ->setData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_CASHIER_ID, $cashier_id)
            ->setData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_OPERATOR_ID, $operator_id)
            ->setData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_CUSTOMER_ID, $customer_id)
            ->setData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_HISTORY_ID,  $history->getId ())
            ->setCustomerGroupId (0)
            ->setCustomerIsGuest (1)
            ->save ()
        ;

        /**
         * NOTE: quote_id instead customer_id
         */
        $customerEmail = Mage::helper ('pdv')->getCustomerEmail ($quote->getId ());

        $customerData = array(
            'mode'      => Mage_Checkout_Model_Type_Onepage::METHOD_GUEST,
            /*
            'entity_id' => $customer->getId (),
            */
            'firstname' => $customer->getFirstname (),
            'lastname'  => $customer->getLastname (),
            'email'     => $customerEmail,
            'taxvat'    => $customer->getTaxvat (),
        );

        Mage::getModel ('checkout/cart_customer_api')->set ($quote->getId (), $customerData, $storeId);

        $customerBillingPostcode  = preg_replace ('[\D]', null, $customerBillingAddress->getPostcode ());
        $customerShippingPostcode = preg_replace ('[\D]', null, $customerShippingAddress->getPostcode ());

        $customerBillingCellphone  = preg_replace ('[\D]', null, $customerBillingAddress->getCellphone ());
        $customerShippingCellphone = preg_replace ('[\D]', null, $customerShippingAddress->getCellphone ());

        Mage::getModel ('checkout/cart_customer_api')->setAddresses ($quote->getId (), array(
            array(
                'mode'       => 'billing',
                'firstname'  => $customerBillingAddress->getFirstname (),
                'lastname'   => $customerBillingAddress->getLastname (),
                'street'     => $customerBillingAddress->getStreet (),
                'city'       => $customerBillingAddress->getCity (),
                'region'     => $customerBillingAddress->getRegionId (),
                'country_id' => $customerBillingAddress->getCountryId (),
                'postcode'   => $customerBillingPostcode,
                'cellphone'  => $customerBillingCellphone,
            ),
            array(
                'mode'       => 'shipping',
                'firstname'  => $customerShippingAddress->getFirstname (),
                'lastname'   => $customerShippingAddress->getLastname (),
                'street'     => $customerShippingAddress->getStreet (),
                'city'       => $customerShippingAddress->getCity (),
                'region'     => $customerShippingAddress->getRegionId (),
                'country_id' => $customerShippingAddress->getCountryId (),
                'postcode'   => $customerShippingPostcode,
                'cellphone'  => $customerShippingCellphone,
            ),
        ), $storeId);

    __returnQuote:

        $cashier->setQuoteId ($quote->getId ())
            ->setCustomerId ($customer->getId ())
            ->save ()
        ;

        return $quote;
    }
}

