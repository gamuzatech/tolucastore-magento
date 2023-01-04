<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = new Mage_Core_Model_Resource_Setup ('pdv_setup');
$installer->startSetup ();

$role = Mage::getModel('api/roles')
    ->setName('Toluca Store PDV')
    ->setRoleType('G')
    ->save();

$resourcesList2D = Mage::getModel('api/roles')->getResourcesList2D();

$resourcesList2D = array_filter($resourcesList2D, function($var) {
    return !strcmp($var, 'all');
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
    /*
    $write->delete ($table);
    */
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
    ->loadByUsername(Toluca_PDV_Helper_Data::DEFAULT_API_USER)
    ->setUsername(Toluca_PDV_Helper_Data::DEFAULT_API_USER)
    ->setFirstname('Toluca Store')
    ->setLastname('PDV')
    ->setEmail('pdv@toluca.com.br')
    ->setApiKey(hash('sha512', uniqid(rand(), true)))
    ->setIsActive(true)
    ->save();

$user->setRoleIds(array($role->getId()))
    ->saveRelations();

$installer->endSetup ();

