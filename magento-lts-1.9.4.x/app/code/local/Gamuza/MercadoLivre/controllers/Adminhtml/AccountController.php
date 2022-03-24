<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * MercadoLivre_Account account controller
 */
class Gamuza_MercadoLivre_Adminhtml_AccountController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed ()
    {
        return Mage::getSingleton ('admin/session')->isAllowed ('gamuza/mercadolivre');
    }

    public function authorizeAction ()
    {
        if (!Mage::getStoreConfigFlag (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ACTIVE))
        {
            Mage::getSingleton ('adminhtml/session')->addError (Mage::helper ('mercadolivre')->__('MercadoLivre is not enabled.'));

            return $this->_redirect ('adminhtml/system_config/edit', array ('section' => 'mercadolivre'));
        }

        $appId = Mage::getStoreConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_APP_ID);

        $redirectUrl = Mage::app ()
            ->getStore (Mage_Core_Model_App::DISTRO_STORE_ID)
            ->getUrl ('mercadolivre/account/redirect')
        ;

        $authorizationUrl = Gamuza_MercadoLivre_Helper_Data::AUTH_AUTHORIZATION_URL
            . '?' . http_build_query (array(
                'response_type' => 'code',
                'client_id'     => $appId,
                'redirect_uri'  => $redirectUrl,
            ))
        ;

        $this->getResponse ()->setRedirect ($authorizationUrl);
    }
}

