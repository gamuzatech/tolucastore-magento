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
class Gamuza_Mobile_Model_Cart_Coupon_Api extends Mage_Checkout_Model_Cart_Coupon_Api
{
    use Gamuza_Mobile_Trait_Api_Resource;

    /**
     * @param  $quoteId
     * @param  $couponCode
     * @param  $storeId
     * @return bool
     */
    public function addCoupon($store = null, $couponCode = null)
    {
        return $this->applyCoupon($store, $couponCode);
    }

    /**
     * @param  $quoteId
     * @param  $storeId
     * @return void
     */
    public function removeCoupon($store = null)
    {
        $couponCode = '';

        return $this->applyCoupon($store, $couponCode);
    }

    /**
     * @param  $quoteId
     * @param  $storeId
     * @return string
     */
    public function getCoupon($store = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        return $quote->getCouponCode();
    }

    /**
     * @param  $quoteId
     * @param  $couponCode
     * @param  $store
     * @return bool
     */
    protected function applyCoupon($store = null, $couponCode = null)
    {
        if (empty ($store))
        {
            $this->_fault ('store_not_specified');
        }

        $quote = $this->_getCustomerQuote($store);

        if (!$quote->getItemsCount())
        {
            $this->_fault('quote_is_empty');
        }

        $oldCouponCode = $quote->getCouponCode();

        if (!is_string($couponCode) || (!strlen($couponCode) && !strlen($oldCouponCode)))
        {
            return false;
        }

        try
        {
            $quote->getShippingAddress()->setCollectShippingRates(true);

            $quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save()
            ;
        }
        catch (Exception $e)
        {
            $this->_fault("cannot_apply_coupon_code", $e->getMessage());
        }

        if ($couponCode)
        {
            if (!$couponCode == $quote->getCouponCode())
            {
                $this->_fault('coupon_code_is_not_valid');
            }
        }

        return true;
    }
}

