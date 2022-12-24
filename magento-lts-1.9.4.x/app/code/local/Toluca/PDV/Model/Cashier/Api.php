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
    public function items ()
    {
        $result = array ();

        $collection = Mage::getModel ('pdv/cashier')->getCollection ();

        $collection->getSelect ()
            ->joinLeft (
                array ('operator' => Mage::getSingleton ('core/resource')->getTableName ('pdv/operator')),
                'main_table.operator_id = operator.entity_id',
                array (
                    'operator_name' => 'operator.name'
                )
            )
            ->joinLeft (
                array ('order' => Mage::getSingleton ('core/resource')->getTableName ('sales/order')),
                'main_table.entity_id = order.pdv_cashier_id AND order.is_pdv = 1',
                array (
                    'order_amount' => 'SUM(order.base_grand_total)'
                )
            )
        ;

        foreach ($collection as $cashier)
        {
            $result [] = array(
                'entity_id'  => intval ($cashier->getId ()),
                'code'       => $cashier->getCode (),
                'name'       => $cashier->getName (),
                'is_active'  => boolval ($cashier->getIsActive ()),
                'status'     => intval ($cashier->getStatus ()),
                'operator_id'    => intval ($cashier->getOperatorId ()),
                'operator_name'  => $cashier->getOperatorName (),
                'created_at' => $cashier->getCreatedAt (),
                'updated_at' => $cashier->getUpdatedAt (),
                'opened_at' => $cashier->getOpenedAt (),
                'closed_at' => $cashier->getClosedAt (),
                'open_amount'      => floatval ($cashier->getOpenAmount ()),
                'reinforce_amount' => floatval ($cashier->getReinforceAmount ()),
                'bleed_amount'     => floatval ($cashier->getBleedAmount ()),
                'money_amount'     => floatval ($cashier->getMoneyAmount ()),
                'change_amount'    => floatval ($cashier->getChangeAmount ()),
                'close_amount'     => floatval ($cashier->getCloseAmount ()),
                'order_amount'     => floatval ($cashier->getOrderAmount ()),
            );
        }

        return $result;
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
            'operator_name'  => $cashier->getOperatorName (),
            'created_at' => $cashier->getCreatedAt (),
            'updated_at' => $cashier->getUpdatedAt (),
            'opened_at' => $cashier->getOpenedAt (),
            'closed_at' => $cashier->getClosedAt (),
            'open_amount'      => floatval ($cashier->getOpenAmount ()),
            'reinforce_amount' => floatval ($cashier->getReinforceAmount ()),
            'bleed_amount'     => floatval ($cashier->getBleedAmount ()),
            'money_amount'     => floatval ($cashier->getMoneyAmount ()),
            'change_amount'    => floatval ($cashier->getChangeAmount ()),
            'close_amount'     => floatval ($cashier->getCloseAmount ()),
            'order_amount'     => floatval ($cashier->getOrderAmount ()),
        );

        $operator = Mage::getModel ('pdv/operator')->load ($cashier->getOperatorId ());

        if ($operator && $operator->getId ())
        {
            $result ['operator_name'] = $operator->getName ();
        }

        $collection = Mage::getModel ('sales/order')->getCollection ()
            ->addFieldToFilter ('is_pdv', array ('eq' => true))
            ->addFieldToFilter ('pdv_cashier_id', array ('eq' => $cashier->getId ()))
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

    public function open ($amount, $operator_id, $password)
    {
        $cashier = $this->_getCashier ($amount, $operator_id, $password);

        if ($cashier->getStatus () == Toluca_PDV_Helper_Data::CASHIER_STATUS_OPENED)
        {
            $this->_fault ('cashier_already_opened');
        }

        $cashier->setStatus (Toluca_PDV_Helper_Data::CASHIER_STATUS_OPENED)
            ->setOperatorId ($operator_id)
            ->setOpenAmount ($amount)
            ->setReinforceAmount (0)
            ->setBleedAmount (0)
            ->setMoneyAmount (0)
            ->setChangeAmount (0)
            ->setCloseAmount (0)
            ->setOpenedAt (date ('c'))
            ->setClosedAt (new Zend_Db_Expr ('NULL'))
            ->save ()
        ;

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_OPEN)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setAmount ($amount)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return intval ($cashier->getId ());
    }

    public function reinforce ($amount, $operator_id, $password)
    {
        $cashier = $this->_getCashier ($amount, $operator_id, $password);

        if ($cashier->getStatus () == Toluca_PDV_Helper_Data::CASHIER_STATUS_CLOSED)
        {
            $this->_fault ('cashier_already_closed');
        }

        $reinforceAmount = floatval ($cashier->getReinforceAmount ());

        $cashier->setReinforceAmount ($reinforceAmount + $amount)
            ->save ()
        ;

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_REINFORCE)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setAmount ($amount)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return true;
    }

    public function bleed ($cashier_id, $amount, $operator_id, $password)
    {
        $cashier = $this->_getCashier ($amount, $operator_id, $password);

        if ($cashier->getStatus () == Toluca_PDV_Helper_Data::CASHIER_STATUS_CLOSED)
        {
            $this->_fault ('cashier_already_closed');
        }

        $openAmount = floatval ($cashier->getOpenAmount ());
        $reinforceAmount = floatval ($cashier->getReinforceAmount ());
        $bleedAmount  = floatval ($cashier->getBleedAmount ());
        $moneyAmount  = floatval ($cashier->getMoneyAmount ());
        $changeAmount = floatval ($cashier->getChangeAmount ());

        $closeAmount = ((($openAmount + $reinforceAmount) - $bleedAmount) + $moneyAmount) - $changeAmount;

        if ($amount > $closeAmount)
        {
            $closeAmount = Mage::helper ('core')->currency ($closeAmount, true, false);

            $message = Mage::helper ('pdv')->__('Bleed amount is invalid. Allowed only %s.', $closeAmount);

            $this->_fault ('cashier_invalid_amount', $message);
        }

        $cashier->setBleedAmount ($bleedAmount + $amount)
            ->save ()
        ;

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_BLEED)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setAmount (- $amount)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return true;
    }

    public function close ($amount, $operator_id, $password)
    {
        $cashier = $this->_getCashier ($amount, $operator_id, $password);

        if ($cashier->getStatus () == Toluca_PDV_Helper_Data::CASHIER_STATUS_CLOSED)
        {
            $this->_fault ('cashier_already_closed');
        }

        $openAmount      = floatval ($cashier->getOpenAmount ());
        $reinforceAmount = floatval ($cashier->getReinforceAmount ());
        $bleedAmount     = floatval ($cashier->getBleedAmount ());
        $moneyAmount     = floatval ($cashier->getMoneyAmount ());
        $changeAmount    = floatval ($cashier->getChangeAmount ());

        $closeAmount = ((($openAmount + $reinforceAmount) - $bleedAmount) + $moneyAmount) - $changeAmount;
        $differenceAmount = $amount - $closeAmount;

        if ($differenceAmount != 0)
        {
            $differenceAmount = Mage::helper ('core')->currency ($differenceAmount, true, false);

            $message = Mage::helper ('pdv')->__('Close amount is invalid. Difference of %s.', $differenceAmount);

            $this->_fault ('cashier_invalid_amount', $message);
        }

        $cashier->setStatus (Toluca_PDV_Helper_Data::CASHIER_STATUS_CLOSED)
            ->setCloseAmount ($amount)
            ->setClosedAt (date ('c'))
            ->save ()
        ;

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_CLOSE)
            ->setCashierId ($cashier->getId ())
            ->setOperatorId ($operator_id)
            ->setAmount (- $amount)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return true;
    }

    public function quote ($cashier_id, $operator_id, $customer_id)
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

        if (!$customerShippingAddress || !$customerShippingAddress->getId ())
        {
            $this->_fault ('customer_shipping_address_not_exists');
        }

        $storeId = Mage_Core_Model_App::DISTRO_STORE_ID;

        $remoteIp = Mage::helper ('core/http')->getRemoteAddr (false);

        $customerPrefix = Mage::getStoreConfig (Toluca_PDV_Helper_Data::XML_PATH_DEFAULT_EMAIL_PREFIX);
        $customerCode   = hash ('crc32', $cashier->getId ()); // use pdv_id instead customer_id
        $customerDomain = Mage::getStoreConfig (Mage_Customer_Model_Customer::XML_PATH_DEFAULT_EMAIL_DOMAIN);
        $customerEmail  = sprintf ('%s+%s@%s', $customerPrefix, $customerCode, $customerDomain);

        $quote = Mage::getModel ('sales/quote')
            ->setStoreId ($storeId)
            ->setIsActive (true)
            ->setIsMultiShipping (false)
            ->setRemoteIp ($remoteIp)
            ->setCustomerFirstname ($customer->getFirstname ())
            ->setCustomerLastname ($customer->getLastname ())
            ->setCustomerEmail ($customerEmail)
            ->setCustomerTaxvat ($customer->getTaxvat ())
            ->save ()
        ;

        $quote->setData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_IS_PDV, true)
            ->setData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_CASHIER_ID, $cashier_id)
            ->setData (Toluca_PDV_Helper_Data::ORDER_ATTRIBUTE_PDV_OPERATOR_ID, $operator_id)
            ->setCustomerGroupId (0)
            ->setCustomerIsGuest (1)
            ->save ()
        ;

        $customerData = array(
            'mode'      => Mage_Checkout_Model_Type_Onepage::METHOD_GUEST,
            'firstname' => $customer->getFirstname (),
            'lastname'  => $customer->getLastname (),
            'email'     => $customerEmail,
            'taxvat'    => $customer->getTaxvat (),
        );

        Mage::getModel ('checkout/cart_customer_api')->set ($quote->getId (), $customerData, $storeId);

        $customerBillingPostcode  = preg_replace ('[\D]', null, $customerBillingAddress->getPostcode ());
        $customerShippingPostcode = preg_replace ('[\D]', null, $customerShippingAddress->getPostcode ());

        $customerBillingFax  = preg_replace ('[\D]', null, $customerBillingAddress->getFax ());
        $customerShippingFax = preg_replace ('[\D]', null, $customerShippingAddress->getFax ());

        Mage::getModel ('checkout/cart_customer_api')->setAddresses ($quote->getId (), array(
            array(
                'mode'       => 'billing',
                'firstname'  => $customerBillingAddress->getFirstname (),
                'lastname'   => $customerBillingAddress->getLastname (),
                'company'    => null,
                'street'     => $customerBillingAddress->getStreet (),
                'city'       => $customerBillingAddress->getCity (),
                'region'     => $customerBillingAddress->getRegionId (),
                'country_id' => $customerBillingAddress->getCountryId (),
                'postcode'   => $customerBillingPostcode,
                'telephone'  => null,
                'fax'        => $customerBillingFax,
            ),
            array(
                'mode'       => 'shipping',
                'firstname'  => $customerShippingAddress->getFirstname (),
                'lastname'   => $customerShippingAddress->getLastname (),
                'company'    => null,
                'street'     => $customerShippingAddress->getStreet (),
                'city'       => $customerShippingAddress->getCity (),
                'region'     => $customerShippingAddress->getRegionId (),
                'country_id' => $customerShippingAddress->getCountryId (),
                'postcode'   => $customerShippingPostcode,
                'telephone'  => null,
                'fax'        => $customerShippingFax,
            ),
        ), $storeId);

        return true;
    }

    protected function _getCashier ($amount, $operator_id, $password)
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
