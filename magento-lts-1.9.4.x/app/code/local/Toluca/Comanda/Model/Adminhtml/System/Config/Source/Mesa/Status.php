<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Used in creating options for Mesa_Status config value selection
 *
 */
class Toluca_Comanda_Model_Adminhtml_System_Config_Source_Mesa_Status
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray ()
    {
        $result = array(
            Toluca_Comanda_Helper_Data::MESA_STATUS_FREE => Mage::helper ('comanda')->__('Free'),
            Toluca_Comanda_Helper_Data::MESA_STATUS_BUSY => Mage::helper ('comanda')->__('Busy'),
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

