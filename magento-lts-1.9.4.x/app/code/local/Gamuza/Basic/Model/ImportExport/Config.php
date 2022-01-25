<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2021 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

/**
 * ImportExport config model
 */
class Gamuza_Basic_Model_ImportExport_Config extends Mage_ImportExport_Model_Config
{
    /**
     * Get model params as combo-box options.
     *
     * @static
     * @param string $configKey
     * @param boolean $withEmpty OPTIONAL Include 'Please Select' option or not
     * @return array
     */
    public static function getModelsComboOptions($configKey, $withEmpty = false)
    {
        $options = array();

        if ($withEmpty)
        {
            $options[] = array('label' => Mage::helper('importexport')->__('-- Please Select --'), 'value' => '');
        }

        foreach (self::getModels($configKey) as $type => $params)
        {
            $options[] = array('value' => $type, 'label' => Mage::helper('importexport')->__($params['label']));
        }

        return $options;
    }
}

