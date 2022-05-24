<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Used in creating options for Limits config value selection
 *
 */
class EasySoftware_ERP_Model_Adminhtml_System_Config_Source_Limit
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = array(
            EasySoftware_ERP_Helper_Data::QUEUE_LIMIT_60  => Mage::helper ('erp')->__('60 items'),
            EasySoftware_ERP_Helper_Data::QUEUE_LIMIT_120 => Mage::helper ('erp')->__('120 items'),
            EasySoftware_ERP_Helper_Data::QUEUE_LIMIT_180 => Mage::helper ('erp')->__('180 items'),
            EasySoftware_ERP_Helper_Data::QUEUE_LIMIT_240 => Mage::helper ('erp')->__('240 items'),
            EasySoftware_ERP_Helper_Data::QUEUE_LIMIT_300 => Mage::helper ('erp')->__('300 items'),
        );

        return $result;
    }

    public function toOptionArray ()
    {
        $result = array ();

        foreach ($this->toArray () as $code => $value)
        {
            $result [] = array ('value' => $code, 'label' => Mage::helper ('erp')->__($value));
        }

        return $result;
    }
}

