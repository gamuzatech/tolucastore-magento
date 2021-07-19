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

$role = Mage::getModel('api/roles')
    ->setName('Toluca Store')
    ->setRoleType('G')
    ->save();

$resourcesList2D = Mage::getModel('api/roles')->getResourcesList2D();

$resourcesList2D = array_filter($resourcesList2D, function($var) {
    return !!strcmp($var, 'all');
});

/*
Mage::getModel('api/rules')
    ->setRoleId($role->getId())
    ->setResources($resourcesList2D)
    ->saveRel();
*/

$row = array(
    'role_id'         => $role->getId(),
    'resource_id'     => 'all',
    'role_type'       => 'G',
    'api_permission'  => 'allow'
);

$resource = Mage::getSingleton ('core/resource');

$write = $resource->getConnection ('core_write');
$table = $resource->getTableName ('api/rule');

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

$user = Mage::getModel('api/user')
    ->loadByUsername('tolucastore')
    ->setUsername('tolucastore')
    ->setFirstname('Toluca')
    ->setLastname('Store')
    ->setEmail('store@toluca.com.br')
    ->setApiKey(hash('sha512', uniqid(rand(), true)))
    ->setIsActive(true)
    ->save();

$user->setRoleIds(array($role->getId()))
    ->saveRelations();

$installer->endSetup ();

