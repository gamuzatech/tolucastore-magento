<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = $this;
$installer->startSetup ();

function addOrUpdateAttribute ($installer, $model, $code, $label)
{
    $installer->addAttribute ($model, $code, array(
        'group'         => Mage::helper ('adminhtml')->__('ERP'),
        'input'         => 'text',
        'type'          => 'varchar',
        'label'         => $label,
        'required'      => 0,
        'user_defined'  => 1,
        'unique'        => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'sort_order'    => 1000,
    ));

    $installer->updateAttribute ($model, $code, 'is_configurable', 0);
    $installer->updateAttribute ($name,  $code, 'is_visible', 1);
}

addOrUpdateAttribute ($installer, 'catalog_category', EasySoftware_ERP_Helper_Data::CATEGORY_ATTRIBUTE_ID, Mage::helper ('erp')->__('ERP Category ID'));
addOrUpdateAttribute ($installer, 'catalog_product',  EasySoftware_ERP_Helper_Data::PRODUCT_ATTRIBUTE_ID,  Mage::helper ('erp')->__('ERP Product ID'));

$installer->endSetup ();

