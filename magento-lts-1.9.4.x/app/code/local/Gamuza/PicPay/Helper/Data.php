<?php
/**
 * @package     Gamuza_PicPay
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

ini_set ('serialize_precision', 4);

class Gamuza_PicPay_Helper_Data extends Mage_Core_Helper_Abstract
{
    const API_PAYMENTS_URL               = 'https://appws.picpay.com/ecommerce/public/payments';
    const API_PAYMENTS_STATUS_URL        = 'https://appws.picpay.com/ecommerce/public/payments/{referenceId}/status';
    const API_PAYMENTS_CANCELLATIONS_URL = 'https://appws.picpay.com/ecommerce/public/payments/{referenceId}/cancellations';

    const API_ECOMMERCE_CHECKOUT_QRCODE  = 'https://appws.picpay.com/ecommerce/checkout/qr-code?order={$orderId}&url={paymentUrl}';

    const API_PAYMENT_STATUS_CREATED     = 'created';
    const API_PAYMENT_STATUS_EXPIRED     = 'expired';
    const API_PAYMENT_STATUS_ANALYSIS    = 'analysis';
    const API_PAYMENT_STATUS_PAID        = 'paid';
    const API_PAYMENT_STATUS_COMPLETED   = 'completed';
    const API_PAYMENT_STATUS_REFUNDED    = 'refunded';
    const API_PAYMENT_STATUS_CHARGEDBACK = 'chagedback';
    const API_PAYMENT_STATUS_ERROR       = 'error';

    const TRANSACTION_TABLE = 'gamuza_picpay_transaction';

    const ORDER_ATTRIBUTE_IS_PICPAY = 'is_picpay';

    const PAYMENT_ATTRIBUTE_EXT_PAYMENT_ID  = 'ext_payment_id';
    const PAYMENT_ATTRIBUTE_PICPAY_STATUS   = 'picpay_status';
    const PAYMENT_ATTRIBUTE_PICPAY_URL      = 'picpay_url';

    const LOG = 'gamuza_picpay.log';

    public function api ($url, $post = null, $request = null, $store = null)
    {
        $xPicPayToken = $this->getStoreConfig ('x_picpay_token', $store);

        $curl = curl_init ();

        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt ($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt ($curl, CURLOPT_HTTPHEADER, array (
            'Content-Type: application/json',
            'x-picpay-token: ' . $xPicPayToken,
        ));
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ($post != null)
        {
            if (empty ($request)) $request = 'POST';

            curl_setopt ($curl, CURLOPT_POST, 1);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, json_encode ($post));
        }

        if ($request != null)
        {
            curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, $request);
        }

        curl_setopt ($curl, CURLOPT_FAILONERROR, true);

        $result   = curl_exec ($curl);
        $info     = curl_getinfo ($curl);
        $response = json_decode ($result);

        $message = null;

        switch ($httpCode = $info ['http_code'])
        {
            case 400: { $message = 'Invalid Request';      break; }
            case 401: { $message = 'Authentication Error'; break; }
            case 403: { $message = 'Permission Denied';    break; }
            case 404: { $message = 'Invalid URL';          break; }
            case 405: { $message = 'Method Not Allowed';   break; }
            case 409: { $message = 'Resource Exists';      break; }
            case 422: { $message = 'Invalid Data';         break; }
            case 500: { $message = 'Internal Error';       break; }
            case 200: { $message = null; /* Success! */    break; }
        }

        if ($error = curl_error ($curl))
        {
            $message = $error;
        }

        if (!empty ($message))
        {
            $message = implode (' : ' , array ($request, $url, json_encode ($post), $message, $result));

            throw Mage::exception ('Gamuza_PicPay', $message, $httpCode);
        }

        curl_close ($curl);

        return $response;
    }

    public function getStoreConfig ($key, $store = null)
    {
        return Mage::getStoreConfig ("payment/gamuza_picpay_payment/{$key}", $store);
    }

    public function getOrderReferenceId ($order)
    {
        $result = Mage::helper ('core')->formatDate(
            $order->getCreatedAt (),
            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
            true
        );

        return sprintf ('%s-%s', $order->getIncrementId (),
            str_replace (array ('/', ':', ' '), '-', $result))
        ;
    }
}

