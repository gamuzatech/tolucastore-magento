<?php
/**
 * @package     Gamuza_PagCripto
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
 * These files are distributed with gamuza_pagcripto-magento at http://github.com/gamuzatech/.
 */

class Gamuza_PagCripto_Block_Payment_Form_Payment extends Mage_Payment_Block_Form
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('gamuza/pagcripto/payment/form/payment.phtml');
    }

    protected function _getConfig ()
    {
        return Mage::getSingleton ('pagcripto/payment_config');
    }

    public function getCcAvailableTypes ()
    {
        $types = $this->_getConfig ()->getCcTypes ();

        if ($method = $this->getMethod ())
        {
            $availableTypes = $method->getConfigData ('cctypes');

            if ($availableTypes)
            {
                $availableTypes = explode (',', $availableTypes);

                foreach ($types as $code => $name)
                {
                    if (!in_array ($code, $availableTypes))
                    {
                        unset ($types [$code]);
                    }
                }
            }
        }

        return $types;
    }

    public function getSkinUrl ($file = null, array $params = array ())
    {
        return parent::getSkinUrl (Gamuza_PagCripto_Helper_Data::PAYMENT_IMAGE_PREFIX . $file);
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        if (is_null($this->_instructions))
        {
            $this->_instructions = $this->getMethod()->getInstructions();
        }

        return $this->_instructions;
    }
}

