<?php
/**
 * @package     Gamuza_OpenPix
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * These files are distributed with gamuza_openpix-magento at http://github.com/gamuzatech/.
 */

class Gamuza_OpenPix_Model_Payment_Method_Payment extends Mage_Payment_Model_Method_Abstract
{
    const CODE = 'gamuza_openpix_payment';

    protected $_code = self::CODE;

    protected $_canOrder = true;

    protected $_formBlockType = 'openpix/payment_form_payment';
    protected $_infoBlockType = 'openpix/payment_info_payment';

    const EXPIRES_IN_SECONDS = 900;

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
        $correlationId = Mage::helper ('openpix')->uuid_v4 ();
        $orderAmount   = intval ($order->getBaseGrandTotal () * 100);

        $storeName = Mage::getStoreConfig (Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);

        $customerName   = sprintf ("%s %s", $order->getCustomerFirstname (), $order->getCustomerLastname ());
        $customerEmail  = $order->getCustomerEmail ();
        $customerPhone  = preg_replace ('[\D]', '', $order->getBillingAddress ()->getFax ());
        $customerTaxvat = preg_replace ('[\D]', '', $order->getCustomerTaxvat ());

        if (empty ($customerTaxvat)) $customerTaxvat = '00000000000';

        $post = array(
            'correlationID' => $correlationId,
            'value'     => $orderAmount,
            'comment'   => $storeName . ' - ' . $order->getIncrementId (),
            'expiresIn' => self::EXPIRES_IN_SECONDS,
            'customer' => array(
                'name'  => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
                'taxID' => $customerTaxvat,
            ),
            'additionalInfo' => array(
                array(
                    'key'   => Mage::helper ('openpix')->__('Store'),
                    'value' => $storeName,
                ),
                array(
                    'key'   => Mage::helper ('openpix')->__('Order'),
                    'value' => $order->getIncrementId (),
                ),
            ),
        );

        try
        {
            $result = Mage::helper ('openpix')->api (Gamuza_OpenPix_Helper_Data::API_PAYMENT_CHARGE_URL, $post, null, $order->getStoreId ());

            $payment->setData (Gamuza_OpenPix_Helper_Data::PAYMENT_ATTRIBUTE_OPENPIX_URL, $result->charge->paymentLinkUrl)
                ->setData (Gamuza_OpenPix_Helper_Data::PAYMENT_ATTRIBUTE_OPENPIX_STATUS, Gamuza_OpenPix_Helper_Data::API_PAYMENT_STATUS_ACTIVE)
                ->setData (Gamuza_OpenPix_Helper_Data::PAYMENT_ATTRIBUTE_OPENPIX_TRANSACTION_ID, $result->charge->transactionID)
                ->setData (Gamuza_OpenPix_Helper_Data::PAYMENT_ATTRIBUTE_OPENPIX_CORRELATION_ID, $correlationId)
                ->save ()
            ;

            $transaction = Mage::getModel ('openpix/transaction')
                ->setStatus (Gamuza_OpenPix_Helper_Data::API_PAYMENT_STATUS_ACTIVE)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setCreatedAt (date ('c'))
                /* Params */
                ->setStoreId ($order->getStoreId ())
                ->setCustomerId ($order->getCustomerId ())
                ->setOrderId ($order->getId ())
                ->setOrderIncrementId ($order->getIncrementId ())
                ->setCorrelationId ($correlationId)
                ->setAmount ($orderAmount)
                ->setExpiresIn (self::EXPIRES_IN_SECONDS)
                /* Result */
                ->setTransactionId ($result->charge->transactionID)
                ->setPaymentLinkId ($result->charge->paymentLinkID)
                ->setPaymentLinkUrl ($result->charge->paymentLinkUrl)
                ->setQrcodeImageUrl ($result->charge->qrCodeImage)
                ->setBrcodeUrl ($result->charge->brCode)
                ->setPixKey ($result->charge->pixKey)
                ->setGlobalId ($result->charge->globalID)
                ->save ()
            ;
        }
        catch (Exception $e)
        {
            throw new Exception (Mage::helper ('openpix')->__('There was an error in the OPENPIX transaction. Please try again!'));
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

