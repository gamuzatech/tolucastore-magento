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
    public function items ($cashier_id, $operator_id, $customer_id)
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

        $result = array ();

        $collection = Mage::getModel ('sales/quote')->getCollection ()
            ->addFieldToFilter ('is_pdv',          array ('eq' => true))
            ->addFieldToFilter ('pdv_cashier_id',  array ('eq' => $cashier->getId ()))
            ->addFieldToFilter ('pdv_operator_id', array ('eq' => $operator->getId ()))
            ->addFieldToFilter ('pdv_customer_id', array ('eq' => $customer_id))
        ;

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
}

