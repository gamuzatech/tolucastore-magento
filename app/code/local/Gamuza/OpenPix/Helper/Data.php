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

ini_set ('serialize_precision', 4);

class Gamuza_OpenPix_Helper_Data extends Mage_Core_Helper_Abstract
{
    const API_PAYMENT_CHARGE_URL = 'https://api.openpix.com.br/api/openpix/v1/charge';
    const API_PAYMENT_STATUS_URL = 'https://api.openpix.com.br/api/openpix/v1/charge/{id}';
    const API_PAYMENT_REFUND_URL = 'https://api.openpix.com.br/api/openpix/v1/refund';
    const API_PAYMENT_QRCODE_URL = 'https://api.openpix.com.br/api/openpix/v1/pixQrCode-static/{id}';

    const API_PAYMENT_STATUS_ACTIVE    = 'ACTIVE';
    const API_PAYMENT_STATUS_COMPLETED = 'COMPLETED';
    const API_PAYMENT_STATUS_EXPIRED   = 'EXPIRED';

    const TRANSACTION_TABLE = 'gamuza_openpix_transaction';

    const ORDER_ATTRIBUTE_IS_OPENPIX = 'is_openpix';

    const PAYMENT_ATTRIBUTE_EXT_PAYMENT_ID = 'ext_payment_id';
    const PAYMENT_ATTRIBUTE_OPENPIX_STATUS = 'openpix_status';
    const PAYMENT_ATTRIBUTE_OPENPIX_URL    = 'openpix_url';
    const PAYMENT_ATTRIBUTE_OPENPIX_TRANSACTION_ID = 'openpix_transaction_id';
    const PAYMENT_ATTRIBUTE_OPENPIX_CORRELATION_ID = 'openpix_correlation_id';

    const LOG = 'gamuza_openpix.log';

    public function api ($url, $post = null, $request = null, $store = null)
    {
        $appId = $this->getStoreConfig ('app_id', $store);

        $curl = curl_init ();

        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt ($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt ($curl, CURLOPT_HTTPHEADER, array (
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $appId,
            'platform: TOLUCASTORE',
            'version: 0.0.1',
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

            throw Mage::exception ('Gamuza_OpenPix', $message, $httpCode);
        }

        curl_close ($curl);

        return $response;
    }

    public function getStoreConfig ($key, $store = null)
    {
        return Mage::getStoreConfig ("payment/gamuza_openpix_payment/{$key}", $store);
    }

    public static function uuid_v4()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}

