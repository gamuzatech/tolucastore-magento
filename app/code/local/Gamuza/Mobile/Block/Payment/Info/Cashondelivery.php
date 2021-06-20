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
 * Cash on delivery payment info
 */
class Gamuza_Mobile_Block_Payment_Info_Cashondelivery extends Mage_Payment_Block_Info
{
    /**
     * Retrieve change type name
     *
     * @return string
     */
    public function getChangeTypeName()
    {
        $types = array(
            0 => Mage::helper('core')->__('No'),
            1 => Mage::helper('core')->__('Yes'),
        );
        $changeType = $this->getInfo()->getAdditionalInformation('change_type');

        if (isset($types[$changeType]))
        {
            return $types[$changeType];
        }

        return (empty($changeType)) ? Mage::helper('payment')->__('N/A') : $changeType;
    }

    /**
     * Prepare cash on delivery related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation)
        {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);
        $data = array();

        if ($changeType = $this->getChangeTypeName())
        {
            $data[Mage::helper('payment')->__('Need Change')] = $changeType;
        }

        if ($this->getInfo()->getAdditionalInformation('change_type') && $this->getInfo()->getAdditionalInformation('cash_amount'))
        {
            $data[Mage::helper('payment')->__('Change To Amount')] = Mage::helper('core')->currency ($this->getInfo()->getAdditionalInformation('cash_amount'), true, false);
        }

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}

