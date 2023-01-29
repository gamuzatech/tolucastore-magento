<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Catalog
 */
$installer = new Mage_Catalog_Model_Resource_Setup ('basic_setup');
$installer->startSetup ();

$installer->updateAttribute ('catalog_category', 'custom_use_parent_settings', 'default_value', '1');
$installer->updateAttribute ('catalog_category', 'is_anchor',                  'default_value', '1');
$installer->updateAttribute ('catalog_category', 'page_layout',                'default_value', 'two_columns_left');

$installer->updateAttribute ('catalog_product', 'sku',               'frontend_input', 'label');
$installer->updateAttribute ('catalog_product', 'sku',               'is_used_for_promo_rules', '1');
$installer->updateAttribute ('catalog_product', 'sku',               'is_visible_on_front',     '1');
$installer->updateAttribute ('catalog_product', 'sku',               'used_in_product_listing', '1');
$installer->updateAttribute ('catalog_product', 'description',       'is_required',    '0');
$installer->updateAttribute ('catalog_product', 'short_description', 'frontend_input', 'text');
$installer->updateAttribute ('catalog_product', 'url_key',           'frontend_input', 'label');
$installer->updateAttribute ('catalog_product', 'tax_class_id',      'default_value',  '0');

$applyToAttributes = array(
    'price',
    'special_price',
    'special_from_date',
    'special_to_date',
    'cost',
    'group_price',
    'tier_price',
    'minimal_price',
    'country_of_manufacture',
    'msrp_enabled',
    'msrp_display_actual_price_type',
    'msrp',
    'tax_class_id',
);

foreach ($applyToAttributes as $attributeCode)
{
    $installer->updateAttribute ('catalog_product', $attributeCode, 'apply_to', 'simple,configurable,virtual,bundle,downloadable,service');
}

$applyToAttributes = array(
    'is_recurring',
    'recurring_profile',
);

foreach ($applyToAttributes as $attributeCode)
{
    $installer->updateAttribute ('catalog_product', $attributeCode, 'apply_to', 'simple,virtual,service');
}

$rootCategoryId = Mage::getModel ('core/store')
    ->load (Mage_Core_Model_App::DISTRO_STORE_ID)
    ->getRootCategoryId ();

$rootCategory = Mage::getModel ('catalog/category')->load ($rootCategoryId)
    ->setIsAnchor (true)
    ->setPageLayout ('two_columns_left')
    ->save ();

/**
 * Customer
 */
$installer = new Mage_Customer_Model_Resource_Setup ('basic_setup');
$installer->startSetup ();

$installer->updateAttribute ('customer', 'middlename', 'is_visible', '0');

$installer->updateAttribute ('customer_address', 'middlename', 'is_visible',      '0');
$installer->updateAttribute ('customer_address', 'company',    'is_visible',      '1');
$installer->updateAttribute ('customer_address', 'street',     'multiline_count', '4');
$installer->updateAttribute ('customer_address', 'region_id',  'is_required',     '1');
$installer->updateAttribute ('customer_address', 'telephone',  'is_required',     '0');
$installer->updateAttribute ('customer_address', 'fax',        'is_required',     '0');

/**
 * General
 */
$countries = array ('BR');

foreach (Mage::helper('directory')->getCountryCollection() as $country)
{
    if ($country->getRegionCollection()->getSize() > 0)
    {
        $countries[] = $country->getId();
    }
}

$coreConfig = Mage::getModel ('core/config');

$coreConfig->deleteConfig (Mage_Directory_Helper_Data::XML_PATH_STATES_REQUIRED);
$coreConfig->deleteConfig (Mage_Directory_Helper_Data::XML_PATH_DISPLAY_ALL_STATES);

$coreConfig->saveConfig (Mage_Directory_Helper_Data::XML_PATH_STATES_REQUIRED, implode(',', $countries));
$coreConfig->saveConfig (Mage_Directory_Helper_Data::XML_PATH_DISPLAY_ALL_STATES, '1');

/**
 * Translation
 */
$pt_BR_SQL = file_get_contents (Mage::getConfig ()->getOptions ()->getLocaleDir () . DS . Gamuza_Basic_Helper_Data::SQL_PT_BR);

Mage::getSingleton ('core/resource')->getConnection ('core_write')->query ($pt_BR_SQL);

$emulation = Mage::getModel ('core/app_emulation');

$oldEnvironment = $emulation->startEnvironmentEmulation(
    Mage_Core_Model_App::DISTRO_STORE_ID,
    Mage_Core_Model_App_Area::AREA_FRONTEND,
    true
);

/**
 * Cms Page  with 'home' identifier page modification for report pages
 */
/** @var Mage_Cms_Model_Page $cms */
$cms = Mage::getModel('cms/page')->load('home', 'identifier');

$homepageTitle = Mage::helper ('page')->__('Home Page');

$homepageContent = <<< HOMEPAGE_CONTENT
<div class="page-title">
<h2>{$homepageTitle}</h2>
</div>
HOMEPAGE_CONTENT;

$reportLayoutUpdate = <<< REPORT_LAYOUT_UPDATE
<reference name="content">
    <block type="catalog/product_new" name="home.catalog.product.new" alias="product_new" template="catalog/product/new.phtml" after="cms_page">
        <action method="addPriceBlockType">
            <type>bundle</type>
            <block>bundle/catalog_product_price</block>
            <template>bundle/catalog/product/price.phtml</template>
        </action>
    </block>
    <block type="reports/product_viewed" name="home.reports.product.viewed" alias="product_viewed" template="reports/home_product_viewed.phtml" after="product_new">
        <action method="addPriceBlockType">
            <type>bundle</type>
            <block>bundle/catalog_product_price</block>
            <template>bundle/catalog/product/price.phtml</template>
        </action>
    </block>
    <block type="reports/product_compared" name="home.reports.product.compared" template="reports/home_product_compared.phtml" after="product_viewed">
        <action method="addPriceBlockType">
            <type>bundle</type>
            <block>bundle/catalog_product_price</block>
            <template>bundle/catalog/product/price.phtml</template>
        </action>
    </block>
</reference>
REPORT_LAYOUT_UPDATE;

$cms->setLayoutUpdateXml($reportLayoutUpdate)
    ->setContent($homepageContent)
    ->setTitle($homepageTitle)
    ->save();

/**
 * Design
 */
$coreConfig->saveConfig ('design/header/welcome', Mage::helper ('page')->__('Default welcome msg!'));

$copyright = Mage::helper ('basic')->__('Toluca Store&trade; is a trademark of Gamuza Technologies.') . '<br/>'
    . Mage::helper ('basic')->__('Copyright &copy; %s Gamuza Technologies. All rights reserved.', date('Y'));

$coreConfig->saveConfig ('design/footer/copyright', $copyright);

/**
 * Group
 */
$tax = Mage::getResourceModel ('tax/class_collection')
    ->addFieldToFilter ('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)
    ->addFieldToFilter ('class_name', 'Retail Customer')
    ->getFirstItem ()
;

$customerGroups = array(
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_NOT_LOGGED_IN => 'Not Logged In',
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_GENERAL => 'General',
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_WHOLESALE => 'Wholesale',
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_RETAILER => 'Retailer',
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_SUPPLIER => 'Supplier',
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_MANUFACTURER => 'Manufacturer',
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_DISTRIBUTER => 'Distributer',
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_SELLER => 'Seller',
    Gamuza_Basic_Helper_Data::CUSTOMER_GROUP_RESELLER => 'Reseller'
);

foreach ($customerGroups as $id => $value)
{
    $name = Mage::helper ('basic')->__($value);

    $group = Mage::getModel ('customer/group')
        ->load ($id)
        ->setCode ($value)
        ->setName ($name)
        ->setIsSystem (true)
        ->setTaxClassId ($tax->getId ())
        ->save()
    ;
}

$emulation->stopEnvironmentEmulation($oldEnvironment);

$installer->endSetup();

