<?php
/**
 * @package     Gamuza_OpenPix
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

/**
 * See the AUTHORS file for a list of people on the Gamuza Team.
 * See the ChangeLog files for a list of changes.
 * These files are distributed with gamuza_openpix-magento at http://github.com/gamuzatech/.
 */

/**
 * Used in creating options for Payment_Status config value selection
 */
class Gamuza_OpenPix_Model_Adminhtml_System_Config_Source_Payment_Status
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = array(
            array ('value' => Gamuza_OpenPix_Helper_Data::API_PAYMENT_STATUS_ACTIVE,    'label' => Mage::helper ('openpix')->__('Active')),
            array ('value' => Gamuza_OpenPix_Helper_Data::API_PAYMENT_STATUS_COMPLETED, 'label' => Mage::helper ('openpix')->__('Completed')),
            array ('value' => Gamuza_OpenPix_Helper_Data::API_PAYMENT_STATUS_EXPIRED,   'label' => Mage::helper ('openpix')->__('Expired')),
        );

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = array(
            Gamuza_OpenPix_Helper_Data::API_PAYMENT_STATUS_ACTIVE    => Mage::helper ('openpix')->__('Active'),
            Gamuza_OpenPix_Helper_Data::API_PAYMENT_STATUS_COMPLETED => Mage::helper ('openpix')->__('Completed'),
            Gamuza_OpenPix_Helper_Data::API_PAYMENT_STATUS_EXPIRED   => Mage::helper ('openpix')->__('Expired'),
        );

        return $result;
    }
}

