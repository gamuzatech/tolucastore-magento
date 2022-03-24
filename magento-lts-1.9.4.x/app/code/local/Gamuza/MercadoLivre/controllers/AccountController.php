<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * MercadoLivre_Account account controller
 */
class Gamuza_MercadoLivre_AccountController extends Mage_Core_Controller_Front_Action
{
    public function redirectAction ()
    {
        $code = $this->getRequest ()->getParam ('code');

        $active = Mage::getStoreConfigFlag (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ACTIVE);

        if (!empty ($code) && $active)
        {
            $appId     = Mage::getStoreConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_APP_ID);
            $secretKey = Mage::getStoreConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_SECRET_KEY);

            $redirectUrl = Mage::app ()
                ->getStore (Mage_Core_Model_App::DISTRO_STORE_ID)
                ->getUrl ('mercadolivre/account/redirect')
            ;

            try
            {
                $apiOAuthTokenUrl = Gamuza_MercadoLivre_Helper_Data::API_OAUTH_TOKEN_URL
                    . '?' . http_build_query (array(
                        'grant_type'    => 'authorization_code',
                        'client_id'     => $appId,
                        'client_secret' => $secretKey,
                        'code'          => $code,
                        'redirect_uri'  => $redirectUrl,
                    ))
                ;

                $result = Mage::helper ('mercadolivre')->api ($apiOAuthTokenUrl, null, 'POST');

                $config = Mage::getModel ('core/config')
                    ->saveConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_AUTH_CODE,     $code)
                    ->saveConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_USER_ID,       $result->user_id)
                    ->saveConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ACCESS_TOKEN,  $result->access_token)
                    ->saveConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_REFRESH_TOKEN, $result->refresh_token)
                ;

                Mage::getSingleton ('core/session')->addSuccess ($this->__('Your MercadoLivre account has been authorized.'));
            }
            catch (Exception $e)
            {
                Mage::getSingleton ('core/session')->addError ($this->__('Invalid MercadoLivre account confirmation code.'));
            }
        }

        $this->_redirect ('/');
    }
}

