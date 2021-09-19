<?php
/**
 * @package     Gamuza_Basic
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
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

$installer = new Mage_Core_Model_Resource_Setup ('basic_setup');
$installer->startSetup ();

$resourcesList2D = Mage::getModel('admin/roles')->getResourcesList2D();

array_shift ($resourcesList2D);

/*
Mage::getModel ('admin/rules')
    ->setRoleId (1)
    ->setResources ($resourcesList2D)
    ->saveRel ();
*/

$row = array(
    'role_id'     => 1,
    'resource_id' => 'all',
    'role_type'   => 'G',
    'permission'  => 'allow'
);

$resource = Mage::getSingleton ('core/resource');

$write = $resource->getConnection ('core_write');
$table = $resource->getTableName ('admin/rule');

$write->beginTransaction ();

try
{
    $write->delete ($table);

    foreach ($resourcesList2D as $resource)
    {
        $row ['resource_id'] = $resource;

        $write->insert ($table, $row);
    }

    $write->commit ();
}
catch (Exception $e)
{
    $write->rollback ();
}

$installer->endSetup ();
