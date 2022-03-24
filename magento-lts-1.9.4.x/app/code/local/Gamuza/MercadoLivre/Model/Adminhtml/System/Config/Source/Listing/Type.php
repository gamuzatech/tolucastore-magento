<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Used in creating options for Listing Type config value selection
 *
 */
class Gamuza_MercadoLivre_Model_Adminhtml_System_Config_Source_Listing_Type
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray ()
    {
        $result = array(
            Gamuza_MercadoLivre_Helper_Data::LISTING_TYPE_FREE         => Mage::helper ('mercadolivre')->__('Free'),
            Gamuza_MercadoLivre_Helper_Data::LISTING_TYPE_GOLD_SPECIAL => Mage::helper ('mercadolivre')->__('Gold Special ( Classic )'),
            Gamuza_MercadoLivre_Helper_Data::LISTING_TYPE_GOLD_PRO     => Mage::helper ('mercadolivre')->__('Gold PRO ( Premium )'),
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

