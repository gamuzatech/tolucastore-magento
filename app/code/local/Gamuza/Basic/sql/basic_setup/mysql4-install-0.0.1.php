<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
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

$installer = new Mage_Core_Model_Resource_Setup ('basic_setup');
$installer->startSetup ();

$coreConfig = Mage::getModel ('core/config');

/**
 * General
 */
$coreConfig->saveConfig (Mage_Core_Helper_Data::XML_PATH_DEFAULT_COUNTRY, 'BR');

$coreConfig->saveConfig ('general/country/allow', 'BR');

$countries = array ('BR');

foreach (Mage::helper('directory')->getCountryCollection() as $country)
{
    if($country->getRegionCollection()->getSize() > 0)
    {
        $countries[] = $country->getId();
    }
}

$coreConfig->saveConfig (Mage_Directory_Helper_Data::XML_PATH_STATES_REQUIRED, implode(',', $countries), 'stores', 1);
$coreConfig->saveConfig (Mage_Directory_Helper_Data::XML_PATH_DISPLAY_ALL_STATES, '0', 'stores', 1);

/**
 * Web
 */
$coreConfig->saveConfig (Mage_Core_Model_Cookie::XML_PATH_COOKIE_LIFETIME, '86400');

/**
 * Design
 */
$coreConfig->saveConfig ('design/head/default_title',       'Toluca Store');
$coreConfig->saveConfig ('design/head/default_description', null);
$coreConfig->saveConfig ('design/head/default_keywords',    null);

$coreConfig->saveConfig ('design/header/logo_src',       'images/logo.png');
$coreConfig->saveConfig ('design/header/logo_src_small', 'images/logo.png');
$coreConfig->saveConfig ('design/header/logo_alt',       'Toluca Store Commerce');

$copyright = Mage::helper ('basic')->__('Toluca Store&trade; is a trademark of Gamuza Technologies.') . '<br/>'
    . Mage::helper ('basic')->__('Copyright &copy; %s Gamuza Technologies. All rights reserved.', date('Y'));

$coreConfig->saveConfig ('design/footer/copyright', $copyright);

/**
 * Catalog
 */
$coreConfig->saveConfig (Mage_Catalog_Helper_Category_Flat::XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY, '1');
$coreConfig->saveConfig (Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT,                  '1');

$coreConfig->saveConfig (Mage_Catalog_Helper_Data::XML_PATH_SEO_SAVE_HISTORY, '0');

$coreConfig->saveConfig (Mage_CatalogSearch_Model_Fulltext::XML_PATH_CATALOG_SEARCH_TYPE, '3');

$coreConfig->saveConfig ('catalog/custom_options/date_fields_order', 'd,m,y');
$coreConfig->saveConfig ('catalog/custom_options/time_format',       '24h');

/**
 * CatalogInventory
 */
$coreConfig->saveConfig (Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK, '1');

$coreConfig->saveConfig ('cataloginventory/item_options/auto_return', '1');

/**
 * Customer
 */
$coreConfig->saveConfig (Mage_Customer_Model_Config_Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE, '0');
$coreConfig->saveConfig (Mage_Customer_Model_Customer::XML_PATH_DEFAULT_EMAIL_DOMAIN, 'toluca.com.br');

$coreConfig->saveConfig ('customer/password/require_admin_user_to_change_user_password', 0);

$coreConfig->saveConfig ('customer/address/street_lines',    '4');
$coreConfig->saveConfig ('customer/address/middlename_show', '0');
$coreConfig->saveConfig ('customer/address/dob_show',        'opt');
$coreConfig->saveConfig ('customer/address/taxvat_show',     'opt');
$coreConfig->saveConfig ('customer/address/gender_show',     'opt');

/**
 * Wishlist
 */
$coreConfig->saveConfig ('wishlist/general/active', '0');

/**
 * Sales
 */
$coreConfig->saveConfig ('sales/minimum_order/active', '1');
$coreConfig->saveConfig ('sales/minimum_order/amount', '0');

$coreConfig->saveConfig ('sales/gift_options/allow_order', '0');
$coreConfig->saveConfig ('sales/gift_options/allow_items', '0');

/**
 * Sales Email
 */
$coreConfig->saveConfig (Mage_Sales_Model_Order::XML_PATH_EMAIL_ENABLED,                   '0');
$coreConfig->saveConfig (Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_ENABLED,            '0');
$coreConfig->saveConfig (Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_ENABLED,           '0');
$coreConfig->saveConfig (Mage_Sales_Model_Order_Invoice::XML_PATH_UPDATE_EMAIL_ENABLED,    '0');
$coreConfig->saveConfig (Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_ENABLED,          '0');
$coreConfig->saveConfig (Mage_Sales_Model_Order_Shipment::XML_PATH_UPDATE_EMAIL_ENABLED,   '0');
$coreConfig->saveConfig (Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_ENABLED,        '0');
$coreConfig->saveConfig (Mage_Sales_Model_Order_Creditmemo::XML_PATH_UPDATE_EMAIL_ENABLED, '0');

/**
 * Tax
 */
$coreConfig->saveConfig (Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY, 'BR');

/**
 * Shipping
 */
$coreConfig->saveConfig (Mage_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID, 'BR');

$coreConfig->saveConfig ('shipping/option/checkout_multiple', '0');

/**
 * Admin
 */
$coreConfig->saveConfig ('admin/security/session_cookie_lifetime',   '86400');
$coreConfig->saveConfig ('admin/security/validate_formkey_checkout', '1');

/**
 * System
 */
$coreConfig->saveConfig ('system/cron/enableRunNow',           '1');
$coreConfig->saveConfig ('system/cron/showCronUserMessage',    '0');
$coreConfig->saveConfig ('system/smtp/disable',                '1');
$coreConfig->saveConfig ('system/log/enable_log',              '2');
$coreConfig->saveConfig ('system/adminnotification/use_https', '2');

/**
 * Advanced
 */
$coreConfig->saveConfig ('advanced/modules_disable_output/Mage_Backup', '0');

/**
 * Dev
 */
$coreConfig->saveConfig ('dev/js/merge_files',      '1');
$coreConfig->saveConfig ('dev/css/merge_css_files', '1');

Mage_AdminNotification_Model_Survey::saveSurveyViewed (true);

/**
 * Hints
 */
$coreConfig->saveConfig ('hints/store_switcher/url',     null);
$coreConfig->saveConfig ('hints/store_switcher/enabled', '0');

$installer->endSetup();

