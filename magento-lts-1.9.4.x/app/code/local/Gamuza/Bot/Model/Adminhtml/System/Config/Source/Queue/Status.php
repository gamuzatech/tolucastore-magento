<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Used in creating options for Queue_Status config value selection
 *
 */
class Gamuza_Bot_Model_Adminhtml_System_Config_Source_Queue_Status
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray ()
    {
        $result = array(
            Gamuza_Bot_Helper_Data::QUEUE_STATUS_PENDING  => Mage::helper ('bot')->__('Pending'),
            Gamuza_Bot_Helper_Data::QUEUE_STATUS_SENDING  => Mage::helper ('bot')->__('Sending'),
            Gamuza_Bot_Helper_Data::QUEUE_STATUS_FINISHED => Mage::helper ('bot')->__('Finished'),
            Gamuza_Bot_Helper_Data::QUEUE_STATUS_CANCELED => Mage::helper ('bot')->__('Canceled'),
            Gamuza_Bot_Helper_Data::QUEUE_STATUS_STOPPED  => Mage::helper ('bot')->__('Stopped'),
        );

        return $result;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray ()
    {
        $result = array ();

        foreach ($this->toArray () as $value => $label)
        {
            $result [] = array ('value' => $value, 'label' => $label);
        }

        return $result;
    }
}

