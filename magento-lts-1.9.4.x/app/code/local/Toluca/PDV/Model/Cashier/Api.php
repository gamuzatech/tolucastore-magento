<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Cashier API
 */
class Toluca_PDV_Model_Cashier_Api extends Mage_Api_Model_Resource_Abstract
{
    const XML_PATH_PDV_CASHIER_INCLUDE_ALL_ORDERS = Toluca_PDV_Helper_Data::XML_PATH_PDV_CASHIER_INCLUDE_ALL_ORDERS;

    protected $_defaultCustomerId = null;

    protected $_includeAllOrders = null;

    public function __construct ()
    {
        // parent::__construct ();

        $this->_defaultCustomerId = Mage::getStoreConfig (Toluca_PDV_Helper_Data::XML_PATH_PDV_SETTING_DEFAULT_CUSTOMER);

        $this->_includeAllOrders = Mage::getStoreConfigFlag (Toluca_PDV_Helper_Data::XML_PATH_PDV_CASHIER_INCLUDE_ALL_ORDERS);
    }

    public function info ($cashier_id)
    {
        if (empty ($cashier_id))
        {
            $this->_fault ('cashier_not_specified');
        }

        $cashier = Mage::getModel ('pdv/cashier')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $cashier_id))
            ->getFirstItem ()
        ;

        if (!$cashier || !$cashier->getId ())
        {
            $this->_fault ('cashier_not_exists');
        }

        $result = array(
            'entity_id'  => intval ($cashier->getId ()),
            'code'       => $cashier->getCode (),
            'name'       => $cashier->getName (),
            'is_active'  => boolval ($cashier->getIsActive ()),
            'status'     => intval ($cashier->getStatus ()),
            'operator_id'    => intval ($cashier->getOperatorId ()),
            'operator_code'  => $cashier->getOperatorCode (),
            'operator_name'  => $cashier->getOperatorName (),
            'history_id' => intval ($cashier->getHistoryId ()),
            'created_at' => $cashier->getCreatedAt (),
            'updated_at' => $cashier->getUpdatedAt (),
            'order_amount'     => floatval ($cashier->getOrderAmount ()),
            'include_all_orders'  => boolval ($this->_includeAllOrders),
            'default_customer_id' => intval ($this->_defaultCustomerId),
            'customer_id' => intval ($cashier->getCustomerId ()),
            'quote_id'    => intval ($cashier->getQuoteId ()),
            'history'     => null,
        );

        $operator = Mage::getModel ('pdv/operator')->load ($cashier->getOperatorId ());

        if ($operator && $operator->getId ())
        {
            $result ['operator_code'] = $operator->getCode ();
            $result ['operator_name'] = $operator->getName ();
        }

        $history = Mage::getModel ('pdv/history')->load ($cashier->getHistoryId ());

        if ($history && $history->getId ())
        {
            $result ['history'] = array(
                'entity_id'        => intval ($history->getId ()),
                'open_amount'      => floatval ($history->getOpenAmount ()),
                'reinforce_amount' => floatval ($history->getReinforceAmount ()),
                'bleed_amount'     => floatval ($history->getBleedAmount ()),
                'close_amount'     => floatval ($history->getCloseAmount ()),
                'opened_at' => $history->getOpenedAt (),
                'closed_at' => $history->getClosedAt (),
                'money_amount'   => floatval ($history->getMoneyAmount ()),
                'change_amount'  => floatval ($history->getChangeAmount ()),
                'machine_amount' => floatval ($history->getMachineAmount ()),
                'pagcripto_amount' => floatval ($history->getPagcriptoAmount ()),
                'picpay_amount'    => floatval ($history->getPicpayAmount ()),
                'openpix_amount'   => floatval ($history->getOpenpixAmount ()),
                'creditcard_amount'   => floatval ($history->getCreditcardAmount ()),
                'billet_amount'       => floatval ($history->getBilletAmount ()),
                'banktransfer_amount' => floatval ($history->getBanktransferAmount ()),
                'check_amount'        => floatval ($history->getCheckAmount ()),
                'shipping_amount' => floatval ($history->getShippingAmount ()),
                'total_amount'    => floatval ($history->getTotalAmount ()),
                'created_at' => $history->getCreatedAt (),
                'updated_at' => $history->getUpdatedAt (),
            );
        }

        $collection = Mage::getModel ('sales/order')->getCollection ()
            ->addFieldToFilter ('is_pdv', array ('eq' => true))
            ->addFieldToFilter ('pdv_cashier_id', array ('eq' => $cashier->getId ()))
            ->addFieldToFilter ('pdv_operator_id', array ('eq' => $cashier->getOperatorId ()))
        ;

        $collection->getSelect ()
            ->columns (array(
                'sum_base_grand_total' => 'SUM(base_grand_total)'
            ))
        ;

        if ($collection->getSize () > 0)
        {
            $result ['order_amount'] = floatval ($collection->getFirstItem ()->getSumBaseGrandTotal ());
        }

        return $result;
    }

    public function draft ($cashier_id, $operation = null)
    {
        if (empty ($cashier_id))
        {
            $this->_fault ('cashier_not_specified');
        }

        $cashier = Mage::getModel ('pdv/cashier')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $cashier_id))
            ->getFirstItem ()
        ;

        if (!$cashier || !$cashier->getId ())
        {
            $this->_fault ('cashier_not_exists');
        }

        $operator = Mage::getModel ('pdv/operator')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $cashier->getOperatorId ()))
            ->getFirstItem ()
        ;

        if (!$operator || !$operator->getId ())
        {
            $this->_fault ('operator_not_exists');
        }

        $history = Mage::getModel ('pdv/history')->load ($cashier->getHistoryId ());

        if (!$history || !$history->getId ())
        {
            $this->_fault ('history_not_exists');
        }

        $collection = Mage::getModel ('sales/order')->getCollection ()
            ->addFieldToFilter ('is_pdv', array ('eq' => true))
            ->addFieldToFilter ('pdv_cashier_id', array ('eq' => $cashier->getId ()))
            ->addFieldToFilter ('pdv_operator_id', array ('eq' => $operator->getId ()))
        ;

        $collection->getSelect ()
            ->columns (array(
                'sum_base_grand_total' => 'SUM(base_grand_total)'
            ))
        ;

        if ($collection->getSize () > 0)
        {
            $history->setOrderAmount (floatval ($collection->getFirstItem ()->getSumBaseGrandTotal ()));
        }

        $result = Mage::app ()
            ->getLayout ()
            ->createBlock ('adminhtml/template')
            ->setArea (Mage_Core_Model_App_Area::AREA_ADMINHTML)
            ->setOperation ($operation)
            ->setCashier ($cashier)
            ->setOperator ($operator)
            ->setHistory ($history)
            ->setTemplate ('toluca/pdv/cashier/draft.phtml')
            ->toHtml ();

        return $result;
    }

    public function clear ($cashier_id)
    {
        if (empty ($cashier_id))
        {
            $this->_fault ('cashier_not_specified');
        }

        $cashier = Mage::getModel ('pdv/cashier')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $cashier_id))
            ->getFirstItem ()
        ;

        if (!$cashier || !$cashier->getId ())
        {
            $this->_fault ('cashier_not_exists');
        }

        $cashier->setQuoteId (0)
            ->setCustomerId (0)
            ->save ()
        ;

        return true;
    }

    public function open ($operator_id, $password, $amount, $message)
    {
        $cashier = $this->_getCashier ($operator_id, $password, $amount);

        if ($cashier->getStatus () == Toluca_PDV_Helper_Data::CASHIER_STATUS_OPENED)
        {
            /*
            $this->_fault ('cashier_already_opened');
            */

            return intval ($cashier->getId ());
        }

        $history = Mage::getModel ('pdv/history')
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setOpenAmount ($amount)
            ->setReinforceAmount (0)
            ->setBleedAmount (0)
            ->setCloseAmount (0)
            ->setOpenedAt (date ('c'))
            ->setMoneyAmount (0)
            ->setChangeAmount (0)
            ->setMachineAmount (0)
            ->setPagcriptoAmount (0)
            ->setPicpayAmount (0)
            ->setOpenpixAmount (0)
            ->setCreditcardAmount (0)
            ->setBilletAmount (0)
            ->setBanktransferAmount (0)
            ->setCheckAmount (0)
            ->setShippingAmount (0)
            ->setTotalAmount (0)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        $cashier->setStatus (Toluca_PDV_Helper_Data::CASHIER_STATUS_OPENED)
            ->setOperatorId ($operator_id)
            ->setHistoryId ($history->getId ())
            ->setQuoteId (0)
            ->setCustomerId (0)
            ->setOpenedAt (date ('c'))
            ->save ()
        ;

        $log = Mage::getModel ('pdv/log')
            ->setTypeId (Toluca_PDV_Helper_Data::LOG_TYPE_OPEN)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setHistoryId ($history->getId ())
            ->setTotalAmount ($amount)
            ->setMessage ($message)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return intval ($cashier->getId ());
    }

    public function reinforce ($operator_id, $password, $amount, $message)
    {
        $cashier = $this->_getCashier ($operator_id, $password, $amount);

        if ($cashier->getStatus () == Toluca_PDV_Helper_Data::CASHIER_STATUS_CLOSED)
        {
            $this->_fault ('cashier_already_closed');
        }

        $history = Mage::getModel ('pdv/history')->load ($cashier->getHistoryId ());

        if (!$history || !$history->getId ())
        {
            $this->_fault ('history_not_exists');
        }

        $reinforceAmount = floatval ($history->getReinforceAmount ());

        $history->setReinforceAmount ($reinforceAmount + $amount)
            ->setUpdatedAt (date ('c'))
            ->save ()
        ;

        $log = Mage::getModel ('pdv/log')
            ->setTypeId (Toluca_PDV_Helper_Data::LOG_TYPE_REINFORCE)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setHistoryId ($history->getId())
            ->setTotalAmount ($amount)
            ->setMessage ($message)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return intval ($cashier->getId ());
    }

    public function bleed ($operator_id, $password, $amount, $message)
    {
        $cashier = $this->_getCashier ($operator_id, $password, $amount);

        if ($cashier->getStatus () == Toluca_PDV_Helper_Data::CASHIER_STATUS_CLOSED)
        {
            $this->_fault ('cashier_already_closed');
        }

        $history = Mage::getModel ('pdv/history')->load ($cashier->getHistoryId ());

        if (!$history || !$history->getId ())
        {
            $this->_fault ('history_not_exists');
        }

        $openAmount = floatval ($history->getOpenAmount ());
        $reinforceAmount = floatval ($history->getReinforceAmount ());
        $bleedAmount  = floatval ($history->getBleedAmount ());
        $moneyAmount  = floatval ($history->getMoneyAmount ());
        $changeAmount = floatval ($history->getChangeAmount ());

        $closeAmount = ((($openAmount + $reinforceAmount) + $bleedAmount) + $moneyAmount) + $changeAmount;

        if ($amount > $closeAmount)
        {
            $closeAmount = Mage::helper ('core')->currency ($closeAmount, true, false);

            $message = Mage::helper ('pdv')->__('Bleed amount is invalid. Allowed only %s.', $closeAmount);

            $this->_fault ('cashier_invalid_amount', $message);
        }

        $history->setBleedAmount ($bleedAmount + (- $amount))
            ->setUpdatedAt (date ('c'))
            ->save ()
        ;

        $log = Mage::getModel ('pdv/log')
            ->setTypeId (Toluca_PDV_Helper_Data::LOG_TYPE_BLEED)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setHistoryId ($history->getId ())
            ->setTotalAmount (- $amount)
            ->setMessage ($message)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return intval ($cashier->getId ());
    }

    public function close ($operator_id, $password, $amount, $message)
    {
        $cashier = $this->_getCashier ($operator_id, $password, $amount);

        if ($cashier->getStatus () == Toluca_PDV_Helper_Data::CASHIER_STATUS_CLOSED)
        {
            $this->_fault ('cashier_already_closed');
        }

        $history = Mage::getModel ('pdv/history')->load ($cashier->getHistoryId ());

        if (!$history || !$history->getId ())
        {
            $this->_fault ('history_not_exists');
        }

        $openAmount      = floatval ($history->getOpenAmount ());
        $reinforceAmount = floatval ($history->getReinforceAmount ());
        $bleedAmount     = floatval ($history->getBleedAmount ());
        $moneyAmount     = floatval ($history->getMoneyAmount ());
        $changeAmount    = floatval ($history->getChangeAmount ());

        $closeAmount = ((($openAmount + $reinforceAmount) + $bleedAmount) + $moneyAmount) + $changeAmount;

        $orderAmount = 0;

        if (Mage::getStoreConfigFlag (self::XML_PATH_PDV_CASHIER_INCLUDE_ALL_ORDERS))
        {
            $machineAmount = floatval ($history->getMachineAmount ());
            $pagcriptoAmount = floatval ($history->getPagcriptoAmount ());
            $picpayAmount    = floatval ($history->getPicpayAmount ());
            $openpixAmount   = floatval ($history->getOpenpixAmount ());
            $creditcardAmount   = floatval ($history->getCreditcardAmount ());
            $billetAmount       = floatval ($history->getBilletAmount ());
            $banktransferAmount = floatval ($history->getBanktransferAmount ());
            $checkAmount        = floatval ($history->getCheckAmount ());

            $orderAmount = $machineAmount
                + $pagcriptoAmount + $picpayAmount + $openpixAmount
                + $creditcardAmount + $billetAmount + $banktransferAmount + $checkAmount
            ;
        }

        $differenceAmount = round ($amount - ($closeAmount + $orderAmount), 4);

        if ($differenceAmount != 0)
        {
            $differenceAmount = Mage::helper ('core')->currency ($differenceAmount, true, false);

            $message = Mage::helper ('pdv')->__('Close amount is invalid. Difference of %s.', $differenceAmount);

            $this->_fault ('cashier_invalid_amount', $message);
        }

        $history->setCloseAmount (- $amount)
            ->setClosedAt (date ('c'))
            ->setUpdatedAt (date ('c'))
            ->save ()
        ;

        $cashier->setStatus (Toluca_PDV_Helper_Data::CASHIER_STATUS_CLOSED)
            ->setHistoryId (0)
            ->setQuoteId (0)
            ->setCustomerId (0)
            ->setClosedAt (date ('c'))
            ->save ()
        ;

        $log = Mage::getModel ('pdv/log')
            ->setTypeId (Toluca_PDV_Helper_Data::LOG_TYPE_CLOSE)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setHistoryId ($history->getId ())
            ->setTotalAmount (- $amount)
            ->setMessage ($message)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return intval ($cashier->getId ());
    }

    public function carts ($cashier_id, $operator_id, $customer_id)
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
                'customer_firstname' => $quote->getFirstname (),
                'customer_lastname'  => $quote->getLastname (),
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

    public function quote ($cashier_id, $operator_id, $customer_id, $quote_id = 0, $message = null)
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

    protected function _getCashier ($operator_id, $password, $amount)
    {
        if (empty ($amount))
        {
            $this->_fault ('amount_not_specified');
        }

        if (!is_numeric ($amount))
        {
            $this->_fault ('cashier_invalid_amount');
        }

        if (empty ($operator_id))
        {
            $this->_fault ('operator_not_specified');
        }

        if (empty ($password))
        {
            $this->_fault ('password_not_specified');
        }

        $operator = Mage::getModel ('pdv/operator')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $operator_id))
            ->getFirstItem ()
        ;

        if (!$operator || !$operator->getId ())
        {
            $this->_fault ('operator_not_exists');
        }

        $password = Mage::helper ('core')->getHash ($password, true);

        if (strcmp ($password, $operator->getPassword ()) != 0)
        {
            $this->_fault ('operator_invalid_password');
        }

        $cashier = Mage::getModel ('pdv/cashier')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $operator->getCashierId ()))
            ->getFirstItem ()
        ;

        if (!$cashier || !$cashier->getId ())
        {
            $this->_fault ('cashier_not_exists');
        }

        return $cashier;
    }
}

