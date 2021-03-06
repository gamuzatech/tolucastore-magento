<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Basic_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CATEGORY_ATTRIBUTE_SKU = 'sku';

    const CUSTOMER_GENDER_MALE   = 1;
    const CUSTOMER_GENDER_FEMALE = 2;

    const ORDER_ATTRIBUTE_IS_APP = 'is_app';
    const ORDER_ATTRIBUTE_IS_BOT = 'is_bot';

    const ORDER_SUFFIX_APP    = 'APP';
    const ORDER_SUFFIX_BOT    = 'BOT';
    const ORDER_SUFFIX_ADMIN  = 'ADMIN';
    const ORDER_SUFFIX_STORE  = 'STORE';
    const ORDER_SUFFIX_OTHER  = 'OTHER';

    const PRODUCT_ATTRIBUTE_BRAND = 'brand';
    const PRODUCT_ATTRIBUTE_COLOR = 'color';
    const PRODUCT_ATTRIBUTE_MANUFACTURER = 'manufacturer';
    const PRODUCT_ATTRIBUTE_SIZE = 'size';
    const PRODUCT_ATTRIBUTE_FREE_SHIPPING = 'free_shipping';
    const PRODUCT_ATTRIBUTE_PRICE_TYPE   = 'price_type';
    const PRODUCT_ATTRIBUTE_SKU_POSITION = 'sku_position';

    const PRODUCT_PRICE_VIEW_PRICE_RANGE    = 0;
    const PRODUCT_PRICE_VIEW_AS_LOW_AS      = 1;
    const PRODUCT_PRICE_VIEW_AS_HIGH_AS     = 2;
    const PRODUCT_PRICE_VIEW_AS_LOW_AS_ONE  = 3;
    const PRODUCT_PRICE_VIEW_AS_HIGH_AS_ONE = 4;
    const PRODUCT_PRICE_VIEW_PRICE_STATIC   = 5;
    const PRODUCT_PRICE_VIEW_PRICE_AVERAGE  = 6;

    const ORDER_STATUS_PREPARING = 'preparing';
    const ORDER_STATUS_PAID      = 'paid';
    const ORDER_STATUS_SHIPPED   = 'shipped';
    const ORDER_STATUS_DELIVERED = 'delivered';
    const ORDER_STATUS_REFUNDED  = 'refunded';

    const SQL_PT_BR = 'pt_BR' . DS . 'sql' . DS . 'sql_pt_br_19_utf8.txt';

    public function getLocaleCode ($scope = 'default', $scope_id = 0)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        return $read->fetchOne(
            " SELECT value FROM core_config_data " .
            " WHERE scope = '$scope' AND scope_id = $scope_id " .
            " AND path = 'general/locale/code'"
        );
    }

    public function getTotals (Mage_Adminhtml_Block_Widget_Grid $grid)
    {
        $fieldsTotals = $grid->_fieldsTotals;

        foreach ($grid->getCollection () as $item)
        {
            foreach ($fieldsTotals as $id => $value)
            {
                $fieldsTotals [$id] += floatval ($item->getData ($id));
            }
        }

        $object = new Varien_Object ();
        $object->addData ($fieldsTotals);

        return $object;
    }

    function isMobile ()
    {
        /*
        $result = Zend_Http_UserAgent_Mobile::match(
            Mage::helper ('core/http')->getHttpUserAgent (),
            $_SERVER
        );

        return $result;
        */
        return preg_match ('/(iPad|iPhone|Darwin|Android|Dalvik)/i', $_SERVER ['HTTP_USER_AGENT']);
    }
}

