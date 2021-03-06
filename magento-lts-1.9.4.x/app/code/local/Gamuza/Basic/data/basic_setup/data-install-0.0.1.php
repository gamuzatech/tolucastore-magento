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

/**
 * Customer
 */
$installer = new Mage_Customer_Model_Resource_Setup ('basic_setup');
$installer->startSetup ();

$installer->updateAttribute ('customer', 'middlename', 'is_visible', '0');

$installer->updateAttribute ('customer_address', 'middlename', 'is_visible',      '0');
$installer->updateAttribute ('customer_address', 'street',     'multiline_count', '4');
$installer->updateAttribute ('customer_address', 'region_id',  'is_required',     '1');
$installer->updateAttribute ('customer_address', 'telephone',  'is_required',     '0');
$installer->updateAttribute ('customer_address', 'fax',        'is_required',     '1');

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
 * Cms Page  with 'home' identifier page modification for report pages
 */
/** @var Mage_Cms_Model_Page $cms */
$cms = Mage::getModel('cms/page')->load('home', 'identifier');

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

$cms->setLayoutUpdateXml($reportLayoutUpdate)->save();

/**
 * Translation
 */
$pt_BR_SQL = file_get_contents (Mage::getConfig ()->getOptions ()->getLocaleDir () . DS . Gamuza_Basic_Helper_Data::SQL_PT_BR);

Mage::getSingleton ('core/resource')->getConnection ('core_write')->query ($pt_BR_SQL);

$installer->endSetup();

