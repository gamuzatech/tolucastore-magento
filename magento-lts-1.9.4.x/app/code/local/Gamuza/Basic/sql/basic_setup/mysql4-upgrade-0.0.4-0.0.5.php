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

$installer = new Mage_Catalog_Model_Resource_Setup ('basic_setup');
$installer->startSetup ();

function updateCatalogCategoryTable ($installer, $model, $comment)
{
    $table = $installer->getTable ($model);

    $installer->getConnection ()
        ->addColumn ($table, 'sku', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 64,
            'nullable' => true,
            'comment'  => 'SKU',
            'after'    => 'parent_id',
        ))
    ;
}

updateCatalogCategoryTable ($installer, 'catalog_category_entity', 'Mage Catalog Category Table');

$installer->addAttribute ('catalog_category', Gamuza_Basic_Helper_Data::CATEGORY_ATTRIBUTE_SKU, array(
    'type'             => 'static',
    'backend'          => 'basic/catalog_category_attribute_backend_sku',
    'label'            => Mage::helper ('basic')->__('SKU'),
    'input'            => 'label',
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'          => true,
    'required'         => false,
    'user_defined'     => false,
    'searchable'       => false,
    'filterable'       => false,
    'comparable'       => false,
    'visible_on_front' => false,
    'unique'           => true,
    'group'            => Mage::helper ('basic')->__('General Information'),
    'sort_order'       => 1000,
));

$rootCategoryId = Mage::getModel ('core/store')
    ->load (Mage_Core_Model_App::DISTRO_STORE_ID)
    ->getRootCategoryId ();

$rootCategory = Mage::getModel ('catalog/category')->load ($rootCategoryId)
    ->setIsAnchor (true)
    ->setPageLayout ('two_columns_left')
    ->save ();

$installer->endSetup ();

