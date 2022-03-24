<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Used in creating options for Shipping Mode config value selection
 *
 */
class Gamuza_MercadoLivre_Model_Adminhtml_System_Config_Source_Shipping_Mode
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray ()
    {
        $result = array(
            Gamuza_MercadoLivre_Helper_Data::SHIPPING_MODE_NOT_SPECIFIED => Mage::helper ('mercadolivre')->__('Not Specified'),
            Gamuza_MercadoLivre_Helper_Data::SHIPPING_MODE_CUSTOM        => Mage::helper ('mercadolivre')->__('Custom'),
            Gamuza_MercadoLivre_Helper_Data::SHIPPING_MODE_ME1           => Mage::helper ('mercadolivre')->__('MercadoEnvios mode 1'),
            Gamuza_MercadoLivre_Helper_Data::SHIPPING_MODE_ME2           => Mage::helper ('mercadolivre')->__('MercadoEnvios mode 2 (label & tracking)'),
        );

        return $result;
    }

    public function toOptionArray ()
    {
        $result = array ();

        foreach ($this->toArray () as $code => $value)
        {
            $result [] = array ('value' => $code, 'label' => Mage::helper ('mercadolivre')->__($value));
        }

        return $result;
    }
}

