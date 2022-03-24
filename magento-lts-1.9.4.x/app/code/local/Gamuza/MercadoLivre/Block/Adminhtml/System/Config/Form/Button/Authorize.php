<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Block_Adminhtml_System_Config_Form_Button_Authorize
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml (Varien_Data_Form_Element_Abstract $element)
    {
        return $this->getButton ()->toHtml ();
    }

    public function getButton()
    {
        $url = Mage::helper('adminhtml')->getUrl('admin_mercadolivre/adminhtml_account/authorize');

        $onclick = Mage::getStoreConfigFlag (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ACTIVE)
            ? "popWin('{$url}', 'mercadolivreAuthorize', 'width=800,height=600,resizable=yes,scrollbars=yes')"
            : "setLocation('{$url}')"
        ;

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setData(
            array(
                'id'      => 'btn-mercadolivre-account-authorize',
                'label'   => $this->helper('adminhtml')->__('Authorize'),
                'onclick' => $onclick,
            )
        );

        return $button;
    }
}

