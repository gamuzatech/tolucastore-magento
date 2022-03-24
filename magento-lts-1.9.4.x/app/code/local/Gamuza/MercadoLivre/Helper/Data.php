<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Helper_Data extends Mage_Core_Helper_Abstract
{
    const AUTH_AUTHORIZATION_URL   = 'https://auth.mercadolivre.com.br/authorization';

    const API_OAUTH_TOKEN_URL      = 'https://api.mercadolibre.com/oauth/token';
    const API_SITES_CATEGORIES_URL = 'https://api.mercadolibre.com/sites/MLB/categories';

    const PRODUCT_TABLE = 'gamuza_mercadolivre_product';

    const CATEGORY_ATTRIBUTE_ID = 'mercadolivre_category_id';

    const PRODUCT_ATTRIBUTE_ID       = 'mercadolivre_product_id';
    const PRODUCT_ATTRIBUTE_CATEGORY = 'mercadolivre_product_category';

    const LISTING_TYPE_FREE         = 'free';
    const LISTING_TYPE_GOLD_SPECIAL = 'gold_special';
    const LISTING_TYPE_GOLD_PRO     = 'gold_pro';

    const SHIPPING_MODE_NOT_SPECIFIED = 'not_specified';
    const SHIPPING_MODE_CUSTOM        = 'custom';
    const SHIPPING_MODE_ME1           = 'me1';
    const SHIPPING_MODE_ME2           = 'me2';

    const QUEUE_LIMIT_30  = 30;
    const QUEUE_LIMIT_60  = 60;
    const QUEUE_LIMIT_90  = 90;
    const QUEUE_LIMIT_120 = 120;
    const QUEUE_LIMIT_150 = 150;
    const QUEUE_LIMIT_180 = 180;
    const QUEUE_LIMIT_210 = 210;
    const QUEUE_LIMIT_240 = 240;
    const QUEUE_LIMIT_270 = 270;
    const QUEUE_LIMIT_300 = 300;

    const QUEUE_STATUS_PENDING = 'pending';
    const QUEUE_STATUS_OKAY    = 'okay';
    const QUEUE_STATUS_ERROR   = 'error';

    const XML_PATH_MERCADOLIVRE_SETTINGS_API_URL          = 'mercadolivre/settings/api_url';
    const XML_PATH_MERCADOLIVRE_SETTINGS_ROOT_CATEGORY_ID = 'mercadolivre/settings/root_category_id';

    const XML_PATH_MERCADOLIVRE_SETTINGS_ACTIVE = 'mercadolivre/settings/active';

    const XML_PATH_MERCADOLIVRE_SETTINGS_APP_ID     = 'mercadolivre/settings/app_id';
    const XML_PATH_MERCADOLIVRE_SETTINGS_SECRET_KEY = 'mercadolivre/settings/secret_key';

    const XML_PATH_MERCADOLIVRE_SETTINGS_AUTH_CODE     = 'mercadolivre/settings/auth_code';
    const XML_PATH_MERCADOLIVRE_SETTINGS_USER_ID       = 'mercadolivre/settings/user_id';
    const XML_PATH_MERCADOLIVRE_SETTINGS_ACCESS_TOKEN  = 'mercadolivre/settings/access_token';
    const XML_PATH_MERCADOLIVRE_SETTINGS_REFRESH_TOKEN = 'mercadolivre/settings/refresh_token';

    const LOG = 'gamuza_mercadolivre.log';

    public function api ($url, $post = null, $request = null)
    {
        $curl = curl_init ();

        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt ($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt ($curl, CURLOPT_HTTPHEADER, array ('Content-Type: application/json'));
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

        curl_close ($curl);

        $message = null;

        if (($httpCode = $info ['http_code']) != 200)
        {
            if ($response && is_object ($response)
                && property_exists ($response, 'error')
                && property_exists ($response, 'message'))
            {
                $message = sprintf ('%s [ %s ] %s', $response->error, $response->message, $httpCode);
            }
        }

        if (!empty ($message))
        {
            $message = implode (' : ' , array ($request, $url, json_encode ($post), $message, $result));

            throw Mage::exception ('Gamuza_MercadoLivre', $message, $httpCode);
        }

        return $response;
    }

    public function getStoreConfig ($key, $store = null)
    {
        return Mage::getStoreConfig ("mercadolivre/settings/{$key}", $store);
    }
}

