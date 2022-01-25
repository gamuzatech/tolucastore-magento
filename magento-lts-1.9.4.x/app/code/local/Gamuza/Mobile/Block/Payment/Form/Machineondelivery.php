<?php
/**
 * @package     Gamuza_Mobile
 * @copyright   Copyright (c) 2018 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * These files are distributed with gamuza_mobile-magento at http://github.com/gamuzatech/.
 */

/**
 * Block for Cash On Delivery payment method form
 */
class Gamuza_Mobile_Block_Payment_Form_Machineondelivery extends Mage_Payment_Block_Form_Cashondelivery
{
    /**
     * Block construction. Set block template.
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('gamuza/mobile/payment/form/machineondelivery.phtml');
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('mobile/payment_config');
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();

        if ($method = $this->getMethod())
        {
            $availableTypes = $method->getConfigData('cctypes');

            if ($availableTypes)
            {
                $availableTypes = explode(',', $availableTypes);

                foreach ($types as $code=>$name)
                {
                    if (!in_array($code, $availableTypes))
                    {
                        unset($types[$code]);
                    }
                }
            }
        }

        return $types;
    }
}

