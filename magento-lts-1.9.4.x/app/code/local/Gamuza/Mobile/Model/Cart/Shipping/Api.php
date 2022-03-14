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
 * Shopping cart api
 */
class Gamuza_Mobile_Model_Cart_Shipping_Api extends Mage_Checkout_Model_Cart_Shipping_Api
{
    use Gamuza_Mobile_Trait_Api_Resource;

    protected $_rateAttributes = array(
        'carrier', 'carrier_title', 'code',
        'method', 'method_description', 'price',
        'error_message', 'method_title', 'carrier_sort_order'
    );

    protected $_intAttributes = array(
        'address_id', 'rate_id', 'carrier_sort_order'
    );

    /**
     * Set an Shipping Method for Shopping Cart
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    public function _setShippingMethod($store = null, $shippingMethod = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        $quoteShippingAddress = $quote->getShippingAddress();

        if(!$quoteShippingAddress || !$quoteShippingAddress->getId())
        {
            $this->_fault("shipping_address_is_not_set");
        }

        $rate = $quote->getShippingAddress()->collectShippingRates()->getShippingRateByCode($shippingMethod);

        if (!$rate)
        {
            $this->_fault('shipping_method_is_not_available');
        }

        try
        {
            $quote->getShippingAddress()->setShippingMethod($shippingMethod);

            $quote->collectTotals()->save();
        }
        catch(Mage_Core_Exception $e)
        {
            $this->_fault('shipping_method_is_not_set', $e->getMessage());
        }

        return true;
    }

    /**
     * Get list of available shipping methods
     *
     * @param  $quoteId
     * @param  $store
     * @return array
     */
    public function _getShippingMethodsList($store=null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        $quoteShippingAddress = $quote->getShippingAddress();

        if (!$quoteShippingAddress || !$quoteShippingAddress->getId())
        {
            $this->_fault("shipping_address_is_not_set");
        }

        $quoteShippingAddress->collectTotals ()->save ();
        $quoteShippingAddress->setCollectShippingRates (true); // FORCE RELOAD

        try
        {
            $quoteShippingAddress->collectShippingRates()->save();

            $groupedRates = $quoteShippingAddress->getGroupedAllShippingRates();

            $ratesResult = array();

            foreach ($groupedRates as $carrierCode => $rates )
            {
                $carrierName  = $carrierCode;
                $carrierTitle = Mage::getStoreConfig("carriers/{$carrierCode}/title");

                if (!is_null($carrierTitle)) $carrierName = $carrierTitle;

                foreach ($rates as $rate)
                {
                    $rateItem = $this->_getAttributes($rate, 'global', $this->_rateAttributes);

                    $rateItem['carrier_name'] = $carrierName;

                    foreach ($this->_intAttributes as $code)
                    {
                        if (array_key_exists ($code, $rateItem))
                        {
                            $rateItem [$code] = intval ($rateItem [$code]);
                        }
                    }

                    $ratesResult[] = $rateItem;

                    unset($rateItem);
                }
            }
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_fault('shipping_methods_list_could_not_be_retrieved', $e->getMessage());
        }

        return $ratesResult;
    }
}

