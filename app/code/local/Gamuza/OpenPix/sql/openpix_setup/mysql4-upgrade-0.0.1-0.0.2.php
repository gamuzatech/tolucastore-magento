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

$installer = new Mage_Sales_Model_Resource_Setup ('openpix_setup');
$installer->startSetup ();

$entities = array(
    'quote',
    'order',
);

$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'visible'  => true,
    'required' => false
);

foreach ($entities as $entity)
{
    $installer->addAttribute ($entity, Gamuza_OpenPix_Helper_Data::ORDER_ATTRIBUTE_IS_OPENPIX, $options);
}

$installer->endSetup ();

