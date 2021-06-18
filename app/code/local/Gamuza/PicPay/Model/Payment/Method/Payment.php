<?php
/**
 * @package     Gamuza_PicPay
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * These files are distributed with gamuza_picpay-magento at http://github.com/gamuzatech/.
 */

class Gamuza_PicPay_Model_Payment_Method_Payment extends Mage_Payment_Model_Method_Abstract
{
    const CODE = 'gamuza_picpay_payment';

    protected $_code = self::CODE;

    protected $_canOrder = true;

    protected $_formBlockType = 'picpay/payment_form_payment';
    protected $_infoBlockType = 'picpay/payment_info_payment';

    const DDI_BRL = '+55';

    /**
     * Order payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function order (Varien_Object $payment, $amount)
    {
        parent::order ($payment, $amount);

        $order = $payment->getOrder ();

        /**
         * Transaction
         */
        $storeCode = Mage::app ()->getStore ($order->getStoreId ())->getCode ();

        $callbackUrl = Mage::getUrl ('picpay/payment/callback', array(
            '_secure' => true,
            '_nosid'  => true,
            '_query'  => array(
                '___store' => $storeCode
            )
        ));

        $customerTaxvat = preg_replace ('[\D]', '', $order->getCustomerTaxvat ());

        if (empty ($customerTaxvat)) $customerTaxvat = '00000000000';

        $customerEmail = $order->getCustomerEmail ();

        $customerPhone = self::DDI_BRL . $order->getBillingAddress ()->getFax ();

        $post = array(
            'referenceId' => $order->getIncrementId (),
            'callbackUrl' => $callbackUrl,
            'returnUrl'   => null,
            'value'       => floatval ($order->getBaseGrandTotal ()),
            'expiresAt'   => null,
            'buyer' => array(
                'firstName' => $order->getCustomerFirstname (),
                'lastName'  => $order->getCustomerLastname (),
                'document'  => $customerTaxvat,
                'email'     => $customerEmail,
                'phone'     => $customerPhone,
            ),
        );

        try
        {
            $result = Mage::helper ('picpay')->api (Gamuza_PicPay_Helper_Data::API_PAYMENTS_URL, $post, null, $order->getStoreId ());

            $payment->setData (Gamuza_PicPay_Helper_Data::PAYMENT_ATTRIBUTE_PICPAY_URL, $result->paymentUrl)
                ->setData (Gamuza_PicPay_Helper_Data::PAYMENT_ATTRIBUTE_PICPAY_STATUS, Gamuza_PicPay_Helper_Data::API_PAYMENT_STATUS_CREATED)
                ->save ()
            ;

            $transaction = Mage::getModel ('picpay/transaction')
                /* Params */
                ->setStoreId ($order->getStoreId ())
                ->setCustomerId ($order->getCustomerId ())
                ->setOrderId ($order->getId ())
                ->setOrderIncrementId ($order->getIncrementId ())
                ->setCallbackUrl ($callbackUrl)
                ->setReturUrl (new Zend_Db_Expr ('NULL'))
                ->setAmount (floatval ($order->getBaseGrandTotal ()))
                ->setBuyerEmail ($customerEmail)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setCreatedAt (date ('c'))
                /* Result */
                ->setExpiresAt ($result->expiresAt)
                ->setPaymentUrl ($result->paymentUrl)
                ->setStatus (Gamuza_PicPay_Helper_Data::API_PAYMENT_STATUS_CREATED)
                ->setAuthorizationId (new Zend_Db_Expr ('NULL'))
                ->setCancellationId (new Zend_Db_Expr ('NULL'))
                ->save ()
            ;
        }
        catch (Exception $e)
        {
            throw new Exception (Mage::helper ('picpay')->__('There was an error in the PICPAY transaction. Please try again!'));
        }

        // $payment->setSkipOrderProcessing (true);
        $payment->setIsTransactionPending (true);

        return $this;
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}

