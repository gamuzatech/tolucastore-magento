<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2019 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = new Mage_Catalog_Model_Resource_Setup('basic_setup');
$installer->startSetup ();

$installer->addAttribute ('catalog_product', Gamuza_Basic_Helper_Data::PRODUCT_ATTRIBUTE_FREE_SHIPPING, array(
    'group'            => Mage::helper ('basic')->__('General'),
    'label'            => Mage::helper ('basic')->__('Free Shipping'),
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source'           => 'eav/entity_attribute_source_table',
    'type'             => 'int',
    'input'            => 'boolean',
    'visible'          => true,
    'required'         => false,
    'user_defined'     => false,
    'searchable'       => false,
    'filterable'       => false,
    'comparable'       => false,
    'visible_on_front' => false,
    'unique'           => false,
    'is_configurable'  => false,
    'sort_order'       => 1000,
));

$installer->endSetup ();

