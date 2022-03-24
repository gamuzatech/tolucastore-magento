<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = $this;
$installer->startSetup ();

$installer->addAttribute ('catalog_category', Gamuza_MercadoLivre_Helper_Data::CATEGORY_ATTRIBUTE_ID, array(
    'type'             => 'varchar',
    'label'            => Mage::helper ('mercadolivre')->__('Category ID'),
    'input'            => 'label',
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'          => true,
    'required'         => false,
    'user_defined'     => false,
    'searchable'       => false,
    'filterable'       => false,
    'comparable'       => false,
    'visible_on_front' => false,
    'unique'           => false,
    'group'            => Mage::helper ('mercadolivre')->__('MercadoLivre'),
));

$installer->addAttribute ('catalog_product', Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_CATEGORY, array(
    'type'             => 'varchar',
    'label'            => Mage::helper ('mercadolivre')->__('Category'),
    'input'            => 'label',
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input_renderer'   => 'mercadolivre/adminhtml_catalog_product_helper_form_category',
    'visible'          => true,
    'required'         => false,
    'user_defined'     => false,
    'searchable'       => false,
    'filterable'       => false,
    'comparable'       => false,
    'visible_on_front' => false,
    'unique'           => false,
    'group'            => Mage::helper ('mercadolivre')->__('MercadoLivre'),
));

$installer->addAttribute ('catalog_product', Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_ID,  array(
    'type'             => 'varchar',
    'label'            => Mage::helper ('mercadolivre')->__('Product ID'),
    'input'            => 'label',
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'          => true,
    'required'         => false,
    'user_defined'     => false,
    'searchable'       => false,
    'filterable'       => false,
    'comparable'       => false,
    'visible_on_front' => false,
    'unique'           => false,
    'group'            => Mage::helper ('mercadolivre')->__('MercadoLivre'),
));

$installer->endSetup ();

