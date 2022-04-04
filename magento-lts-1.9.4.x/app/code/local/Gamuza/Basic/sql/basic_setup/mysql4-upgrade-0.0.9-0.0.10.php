<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
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

