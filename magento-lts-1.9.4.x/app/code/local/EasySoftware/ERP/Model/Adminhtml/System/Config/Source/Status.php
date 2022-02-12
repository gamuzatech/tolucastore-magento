<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Used in creating options for Status config value selection
 *
 */
class EasySoftware_ERP_Model_Adminhtml_System_Config_Source_Status
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = array(
            EasySoftware_ERP_Helper_Data::STATUS_PENDING => Mage::helper ('erp')->__('Pending'),
            EasySoftware_ERP_Helper_Data::STATUS_OKAY    => Mage::helper ('erp')->__('Okay'),
            EasySoftware_ERP_Helper_Data::STATUS_ERROR   => Mage::helper ('erp')->__('Error'),
        );

        return $result;
    }
}

