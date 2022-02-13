<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2019 Gamuza Technologies (http://www.gamuza.com.br/)
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

$installer = new Mage_Catalog_Model_Resource_Setup('basic_setup');
$installer->startSetup ();

$installer->addAttribute ('catalog_product', Gamuza_Basic_Helper_Data::PRODUCT_ATTRIBUTE_BRAND, array(
    'group'            => Mage::helper ('basic')->__('General'),
    'label'            => Mage::helper ('basic')->__('Brand'),
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source'           => 'eav/entity_attribute_source_table',
    'type'             => 'int',
    'input'            => 'select',
    'visible'          => true,
    'required'         => false,
    'user_defined'     => true,
    'searchable'       => true,
    'filterable'       => true,
    'comparable'       => true,
    'visible_on_front' => true,
    'unique'           => false,
    'is_configurable'  => false,
    'sort_order'       => 1000,
));

$installer->addAttribute ('catalog_product', Gamuza_Basic_Helper_Data::PRODUCT_ATTRIBUTE_COLOR, array(
    'group'            => Mage::helper ('basic')->__('General'),
    'label'            => Mage::helper ('basic')->__('Color'),
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source'           => 'eav/entity_attribute_source_table',
    'type'             => 'int',
    'input'            => 'select',
    'visible'          => true,
    'required'         => false,
    'user_defined'     => true,
    'searchable'       => true,
    'filterable'       => true,
    'comparable'       => true,
    'visible_on_front' => true,
    'unique'           => false,
    'is_configurable'  => false,
    'sort_order'       => 1000,
));

$installer->addAttribute ('catalog_product', Gamuza_Basic_Helper_Data::PRODUCT_ATTRIBUTE_SIZE, array(
    'group'            => Mage::helper ('basic')->__('General'),
    'label'            => Mage::helper ('basic')->__('Size'),
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source'           => 'eav/entity_attribute_source_table',
    'type'             => 'int',
    'input'            => 'select',
    'visible'          => true,
    'required'         => false,
    'user_defined'     => true,
    'searchable'       => true,
    'filterable'       => true,
    'comparable'       => true,
    'visible_on_front' => true,
    'unique'           => false,
    'is_configurable'  => true,
    'sort_order'       => 1000,
));

$installer->endSetup ();

