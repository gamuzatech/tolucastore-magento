<?php
/*
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2023 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = new Mage_Customer_Model_Entity_Setup('basic_setup');
$installer->startSetup ();

$installer->addAttribute(
    'customer',
    Gamuza_Basic_Helper_Data::CUSTOMER_ATTRIBUTE_SECONDARY_NAME,
    array(
        'type'         => 'varchar',
        'length'       => 255,
        'input'        => 'text',
        'label'        => Mage::helper ('basic')->__('Secondary Name'),
        'visible'      => true,
        'required'     => false,
        'user_defined' => false,
        'unique'       => false,
    )
);

$forms = array(
    'adminhtml_customer',
    'adminhtml_checkout',
    'customer_account_create',
    'customer_account_edit',
    'checkout_register',
);

$attribute = Mage::getSingleton ('eav/config')->getAttribute(
    $installer->getEntityTypeId ('customer'), Gamuza_Basic_Helper_Data::CUSTOMER_ATTRIBUTE_SECONDARY_NAME)
;
$attribute->setData ('used_in_forms', $forms)
    ->setData('is_system', true)
    ->setData('sort_order', 71)
;
$attribute->save ();

$installer->endSetup ();

