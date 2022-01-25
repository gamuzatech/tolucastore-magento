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

class Gamuza_OpenPix_Block_Payment_Info_Payment extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('gamuza/openpix/payment/info/default.phtml');
    }

    /**
     * Prepare credit card related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);
        $data = array();

        if ($openpixStatus = $this->getInfo()->getOpenpixStatus()) {
            $data[Mage::helper('payment')->__('Status')] = $this->_ucwords ($openpixStatus);
        }

        $amount = Mage::helper('core')->currency($this->getInfo()->getBaseAmountOrdered(), true, false);
        $data[Mage::helper('payment')->__('Amount')] = $amount;

        if ($openpixUrl = $this->getInfo()->getOpenpixUrl()) {
            $data[Mage::helper('payment')->__('Payment URL')] = $this->_url($openpixUrl);
        }

        if (Mage::app()->getStore()->isAdmin()) {
            if ($extPaymentId = $this->getExtPaymentId()) {
                $data[Mage::helper('payment')->__('Ext. Payment ID')] = $extPaymentId;
            }
        } // isAdmin

        return $transport->setData(array_merge($transport->getData(), $data));
    }

    /**
     * Retrieve External Payment ID
     *
     * @return string
     */
    public function getExtPaymentId()
    {
        return $this->getInfo()->getData(Gamuza_OpenPix_Helper_Data::PAYMENT_ATTRIBUTE_EXT_PAYMENT_ID);
    }

    public function _ucwords ($text)
    {
        return Mage::helper ('openpix')->__(ucwords (str_replace ('_', ' ', $text)));
    }

    public function _url ($url)
    {
        return Mage::helper ('openpix')->__("<a target='_blank' href='%s'>See on OpenPix</a>", $url);
    }
}

