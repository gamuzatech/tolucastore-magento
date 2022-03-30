<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Helper_Data extends Mage_Core_Helper_Abstract
{
    const GROUP_TABLE    = 'easysoftware_erp_group';
    const BRAND_TABLE    = 'easysoftware_erp_brand';
    const PRODUCT_TABLE  = 'easysoftware_erp_product';
    const CUSTOMER_TABLE = 'easysoftware_erp_customer';
    const ORDER_TABLE    = 'easysoftware_erp_order';

    const CATEGORY_ATTRIBUTE_ID = 'erp_category_id';

    const PRODUCT_ATTRIBUTE_ID = 'erp_product_id';

    const QUEUE_LIMIT_30  = 30;
    const QUEUE_LIMIT_60  = 60;
    const QUEUE_LIMIT_90  = 90;
    const QUEUE_LIMIT_120 = 120;
    const QUEUE_LIMIT_150 = 150;
    const QUEUE_LIMIT_180 = 180;
    const QUEUE_LIMIT_210 = 210;
    const QUEUE_LIMIT_240 = 240;
    const QUEUE_LIMIT_270 = 270;
    const QUEUE_LIMIT_300 = 300;

    const STATUS_PENDING = 'pending';
    const STATUS_OKAY    = 'okay';
    const STATUS_ERROR   = 'error';

    const LOG = 'easysoftware_erp.log';

    public function query ($query, $args = null)
    {
        $host     = $this->getFirebirdConfig ('host');
        $port     = $this->getFirebirdConfig ('port');
        $database = $this->getFirebirdConfig ('database');
        $username = $this->getFirebirdConfig ('username');
        $password = $this->getFirebirdConfig ('password');

        $database = sprintf ("%s/%s:%s", $host, $port, $database);

        $connection = ibase_connect(
            $database,
            $username,
            $password
        );

        return ibase_query ($connection, $query);
    }

    public function mb_strtolower ($text)
    {
        mb_internal_encoding ('UTF-8');

        return mb_strtolower ($text);
    }

    public function ucfirst ($text)
    {
        return ucfirst ($this->mb_strtolower ($text));
    }

    public function getFirebirdConfig ($key, $store = null)
    {
        return Mage::getStoreConfig ("erp/firebird/{$key}", $store);
    }

    public function getProductConfig ($key, $store = null)
    {
        return Mage::getStoreConfig ("erp/product/{$key}", $store);
    }

    public function getOrderConfig ($key, $store = null)
    {
        return Mage::getStoreConfig ("erp/order/{$key}", $store);
    }

    public function getQueueConfig ($key, $store = null)
    {
        return Mage::getStoreConfig ("erp/queue/{$key}", $store);
    }

    public function getStoreConfig ($key, $store = null)
    {
        return Mage::getStoreConfig ("erp/settings/{$key}", $store);
    }
}

