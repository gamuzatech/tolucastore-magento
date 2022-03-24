<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Model_Cron_Token extends Gamuza_MercadoLivre_Model_Cron_Abstract
{
    public function refresh ()
    {
        if (!$this->getStoreConfig ('active'))
        {
            return $this;
        }

        $appId        = $this->getStoreConfig ('app_id');
        $secretKey    = $this->getStoreConfig ('secret_key');
        $refreshToken = $this->getStoreConfig ('refresh_token');

        $post = array(
            'grant_type'    => 'refresh_token',
            'client_id'     => $appId,
            'client_secret' => $secretKey,
            'refresh_token' => $refreshToken
        );

        try
        {
            $result = $this->getHelper ()->api (Gamuza_MercadoLivre_Helper_Data::API_OAUTH_TOKEN_URL, $post);

            $config = Mage::getModel ('core/config')
                ->saveConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ACCESS_TOKEN,  $result->access_token)
                ->saveConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_REFRESH_TOKEN, $result->refresh_token)
            ;
        }
        catch (Exception $e)
        {
            $this->message ($e->getMessage ());
        }
    }
}

