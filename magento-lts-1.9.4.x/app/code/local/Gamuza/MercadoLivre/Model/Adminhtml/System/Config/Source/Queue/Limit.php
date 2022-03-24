<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Used in creating options for QueueLimits config value selection
 *
 */
class Gamuza_MercadoLivre_Model_Adminhtml_System_Config_Source_Queue_Limit
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray ()
    {
        $result = array(
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_30  => Mage::helper ('mercadolivre')->__('30 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_60  => Mage::helper ('mercadolivre')->__('60 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_90  => Mage::helper ('mercadolivre')->__('90 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_120 => Mage::helper ('mercadolivre')->__('120 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_150 => Mage::helper ('mercadolivre')->__('150 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_180 => Mage::helper ('mercadolivre')->__('180 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_210 => Mage::helper ('mercadolivre')->__('210 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_240 => Mage::helper ('mercadolivre')->__('240 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_270 => Mage::helper ('mercadolivre')->__('270 items'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_LIMIT_300 => Mage::helper ('mercadolivre')->__('300 items'),
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

