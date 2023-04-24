<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = new Mage_Core_Model_Resource_Setup ('basic_setup');
$installer->startSetup ();

$role = Mage::getModel ('admin/roles')
    ->load (Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_NAME, 'role_name')
    ->setName (Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_NAME)
    ->setRoleType ('G')
    ->save ();

$resourcesList2D = Mage::getModel('admin/roles')->getResourcesList2D();

$resourcesList2D = array_filter ($resourcesList2D, function($var) {
    return !!strcmp ($var, 'all');
});

/*
Mage::getModel ('admin/rules')
    ->setRoleId (1)
    ->setResources ($resourcesList2D)
    ->saveRel ();
*/

$row = array(
    'role_id'     => $role->getId (),
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

$firstName = strrstr (Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_NAME, ' ', true);
$lastName  = trim (strrstr (Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_NAME, ' '));

$user = Mage::getModel ('admin/user')
    ->loadByUsername (Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_USER)
    ->setUsername (Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_USER)
    ->setFirstname ($firstName)
    ->setLastname ($lastName)
    ->setEmail (Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_EMAIL)
    ->setApiKey (hash ('sha512', uniqid(rand (), true)))
    ->setIsActive (true)
    ->save ();

$user->setRoleIds (array ($role->getId ()))
    ->saveRelations ();

$installer->endSetup ();

