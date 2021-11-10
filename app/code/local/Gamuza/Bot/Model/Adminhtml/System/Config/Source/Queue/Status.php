<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
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
            Gamuza_Bot_Helper_Data::STATUS_CATEGORY => Mage::helper ('bot')->__('Category'),
            Gamuza_Bot_Helper_Data::STATUS_PRODUCT  => Mage::helper ('bot')->__('Product'),
            Gamuza_Bot_Helper_Data::STATUS_OPTION   => Mage::helper ('bot')->__('Option'),
            Gamuza_Bot_Helper_Data::STATUS_VALUE    => Mage::helper ('bot')->__('Value'),
            Gamuza_Bot_Helper_Data::STATUS_BUNDLE   => Mage::helper ('bot')->__('Bundle'),
            Gamuza_Bot_Helper_Data::STATUS_SELECTION => Mage::helper ('bot')->__('Selection'),
            Gamuza_Bot_Helper_Data::STATUS_COMMENT  => Mage::helper ('bot')->__('Comment'),
            Gamuza_Bot_Helper_Data::STATUS_CART     => Mage::helper ('bot')->__('Cart'),
            Gamuza_Bot_Helper_Data::STATUS_ADDRESS  => Mage::helper ('bot')->__('Address'),
            Gamuza_Bot_Helper_Data::STATUS_SHIPPING => Mage::helper ('bot')->__('Shipping'),
            Gamuza_Bot_Helper_Data::STATUS_PAYMENT  => Mage::helper ('bot')->__('Payment'),
            Gamuza_Bot_Helper_Data::STATUS_PAYMENT_CASH    => Mage::helper ('bot')->__('Payment Cash'),
            Gamuza_Bot_Helper_Data::STATUS_PAYMENT_MACHINE => Mage::helper ('bot')->__('Payment Machine'),
            Gamuza_Bot_Helper_Data::STATUS_CHECKOUT => Mage::helper ('bot')->__('Checkout'),
            Gamuza_Bot_Helper_Data::STATUS_ORDER    => Mage::helper ('bot')->__('Order'),
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

