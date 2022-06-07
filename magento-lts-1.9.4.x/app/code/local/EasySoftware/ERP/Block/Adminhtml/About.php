<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Block_Adminhtml_About
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render (Varien_Data_Form_Element_Abstract $element)
    {
        $options = array(
            0 => Mage::helper ('core')->__('No'),
            1 => Mage::helper ('core')->__('Yes'),
        );

        $interbase = intval (extension_loaded('interbase'));

$html = <<< HTML
    Interbase: {$options [$interbase]}<br/>
HTML;

        $active = Mage::helper ('erp')->getStoreConfig ('active');
        $html  .= Mage::helper ('erp')->__('Active: %s', $options [$active]) . '<br/>';

        if (!$active)
        {
            return $html;
        }

        $host = Mage::helper ('erp')->getFirebirdConfig ('host');
        $port = Mage::helper ('erp')->getFirebirdConfig ('port');

        $fp = fsockopen ($host, $port, $code, $message, 1);

        $html .= Mage::helper ('erp')->__('Connected: %s', $options [$fp ? 1 : 0]) . '<br/>';

        if (!$fp)
        {
            $html .= sprintf ('%s: %s', $message, $code);

            return $html;
        }
        else
        {
            fclose ($fp);
        }

        $result = Mage::helper ('erp')->query ('SELECT FIRST 1 * FROM CONFIG');

        $row = ibase_fetch_object ($result);

        $html .= Mage::helper ('erp')->__('Company: %s', $row->EMPRESA) . '<br/>';
        $html .= Mage::helper ('erp')->__('Version: %s', $row->VERSAO) . '<br/>';

        $result = Mage::helper ('erp')->query ('SELECT FIRST 1 * FROM EMPRESA');

        $row = ibase_fetch_object ($result);

        $html .= Mage::helper ('erp')->__('Company: %s', $row->RAZAO) . '<br/>';
        $html .= Mage::helper ('erp')->__('Fantasy: %s', $row->FANTASIA) . '<br/>';
        $html .= Mage::helper ('erp')->__('Code: %s', $row->CODIGO) . '<br/>';

        return $html;
    }
}

