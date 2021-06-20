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

/**
 * Shopping cart api for customer data
 */
class Gamuza_Mobile_Model_Cart_Customer_Api extends Mage_Checkout_Model_Cart_Customer_Api
{
    use Gamuza_Mobile_Trait_Api_Resource;

    protected $_ignoredAttributeCodes = array(
        'customer_id', 'website_id', 'store_id', 'group_id',
    );

    /**
     * @param  int $quoteId
     * @param  array of array|object $customerAddressData
     * @param  int|string $store
     * @return int
     */
    public function _setAddresses($store = null, $customerAddressData = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        $customerAddressData = $this->_prepareCustomerAddressData($customerAddressData);

        if (is_null($customerAddressData))
        {
            $this->_fault('customer_address_data_empty');
        }

        foreach ($customerAddressData as $addressItem)
        {
//            switch($addressItem['mode']) {
//            case self::ADDRESS_BILLING:
                /** @var $address Mage_Sales_Model_Quote_Address */
                $address = Mage::getModel("sales/quote_address");
//                break;
//            case self::ADDRESS_SHIPPING:
//                /** @var $address Mage_Sales_Model_Quote_Address */
//                $address = Mage::getModel("sales/quote_address");
//                break;
//            }
            $addressMode = $addressItem['mode'];

            unset($addressItem['mode']);

            if (!empty($addressItem['entity_id']))
            {
                $customerAddress = $this->_getCustomerAddress($addressItem['entity_id']);

                if ($customerAddress->getCustomerId() != $quote->getCustomerId())
                {
                    $this->_fault('address_not_belong_customer');
                }

                $address->importCustomerAddress($customerAddress);
            }
            else
            {
                $address->setData($addressItem);
            }

            $address->implodeStreetAddress();

            if (($validateRes = $address->validate())!==true)
            {
                $this->_fault('customer_address_invalid', implode(PHP_EOL, $validateRes));
            }

            switch($addressMode)
            {
                case self::ADDRESS_BILLING:
                {
                    $address->setEmail($quote->getCustomer()->getEmail());

                    if (!$quote->isVirtual())
                    {
                        $usingCase = isset($addressItem['use_for_shipping']) ? (int)$addressItem['use_for_shipping'] : 0;

                        switch($usingCase)
                        {
                            case 0:
                            {
                                $shippingAddress = $quote->getShippingAddress();
                                $shippingAddress->setSameAsBilling(0);

                                break;
                            }
                            case 1:
                            {
                                $billingAddress = clone $address;
                                $billingAddress->unsAddressId()->unsAddressType();

                                $shippingAddress = $quote->getShippingAddress();
                                $shippingMethod = $shippingAddress->getShippingMethod();

                                $shippingAddress->addData($billingAddress->getData())
                                    ->setSameAsBilling(1)
                                    ->setShippingMethod($shippingMethod)
                                    ->setCollectShippingRates(true)
                                ;

                                break;
                            }
                        }
                    }

                    $quote->setBillingAddress($address);

                    break;
                }
                case self::ADDRESS_SHIPPING:
                {
                    $address->setCollectShippingRates(true)
                        ->setSameAsBilling(0)
                    ;

                    $quote->setShippingAddress($address);

                    break;
                }
            }

        }

        try
        {
            $quote->collectTotals()->save();
        }
        catch (Exception $e)
        {
            $this->_fault('address_is_not_set', $e->getMessage());
        }

        return true;
    }
}

