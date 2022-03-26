<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Block_Adminhtml_System_Config_Form_Text_Application
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml (Varien_Data_Form_Element_Abstract $element)
    {
        $online = Mage::helper ('mercadolivre')->__('Your store needs to be online!');

        $redirectMsg = Mage::helper ('mercadolivre')->__('Redirect URI:');
        $redirectUrl = Mage::app ()
            ->getStore (Mage_Core_Model_App::DISTRO_STORE_ID)
            ->getUrl ('mercadolivre/account/redirect')
        ;

        $notificationMsg = Mage::helper ('mercadolivre')->__('Notification URI:');
        $notificationUrl = Mage::app ()
            ->getStore (Mage_Core_Model_App::DISTRO_STORE_ID)
            ->getUrl ('mercadolivre/account/notification')
        ;

$html = <<< HTML
    <p><small>{$element->getComment ()}</small></p>
    <p><small>{$online}</small></p>
    <p><small>{$redirectMsg}</small></br>{$redirectUrl}</p>
    <p><small>{$notificationMsg}</small></br>{$notificationUrl}</p>
HTML;

        return $element->setComment ($html)->getElementHtml ();
    }
}

