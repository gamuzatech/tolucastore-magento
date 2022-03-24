<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Used in creating options for Queue Status config value selection
 *
 */
class Gamuza_MercadoLivre_Model_Adminhtml_System_Config_Source_Queue_Status
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray ()
    {
        $result = array(
            Gamuza_MercadoLivre_Helper_Data::QUEUE_STATUS_PENDING => Mage::helper ('mercadolivre')->__('Pending'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_STATUS_OKAY    => Mage::helper ('mercadolivre')->__('Okay'),
            Gamuza_MercadoLivre_Helper_Data::QUEUE_STATUS_ERROR   => Mage::helper ('mercadolivre')->__('Error'),
        );

        return $result;
    }
}

