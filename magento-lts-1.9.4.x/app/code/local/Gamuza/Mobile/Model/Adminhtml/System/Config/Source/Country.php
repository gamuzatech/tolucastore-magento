<?php
/**
 * @package     Gamuza_Mobile
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
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

class Gamuza_Mobile_Model_Adminhtml_System_Config_Source_Country
    extends Mage_Adminhtml_Model_System_Config_Source_Country
{
    protected $_options;

    public function toArray ()
    {
        $result = array ();

        foreach ($this->toOptionArray () as $country)
        {
            $result [$country ['value']] = $country ['label'];
        }

        return $result;
    }

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options)
        {
            $this->_options = Mage::getResourceModel('directory/country_collection')
                ->addFieldToFilter('country_id', 'BR')
                ->loadData()
                ->toOptionArray(false)
            ;
        }

        $options = $this->_options;

        if(!$isMultiselect)
        {
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;
    }
}

