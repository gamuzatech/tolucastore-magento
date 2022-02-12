<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Cron_Abstract
{
    protected $_debug = false;

    public function __construct ()
    {
        $this->_debug = $this->getStoreConfig ('debug');

        Mage::app ()->getTranslator ()->init (Mage_Core_Model_App_Area::AREA_ADMINHTML, true);

        if (!$this->isCli ()) $this->message ('<pre>');

        $this->message ('+ Begin : ' . strftime ('%c'));

        static::_construct ();
    }

    public function _construct () {}

    public function __destruct ()
    {
        $this->message ('= End : ' . strftime ('%c'));

        if (!$this->isCli ()) $this->message ('</pre>');
    }

    protected function getHelper ()
    {
        return Mage::helper ('erp');
    }

    protected function getQueueConfig ($key, $storeId = null)
    {
        return $this->getHelper ()->getQueueConfig ($key, $storeId);
    }

    protected function getStoreConfig ($key, $storeId = null)
    {
        return $this->getHelper ()->getStoreConfig ($key, $storeId);
    }

    protected function isCli ()
    {
        if (!strcmp (php_sapi_name (), 'cli') && empty ($_SERVER ['REMOTE_ADDR']))
        {
            return true;
        }
    }

    protected function logException (Exception $e)
    {
        Mage::log (PHP_EOL . $e->__toString (), Zend_Log::ERR, EasySoftware_ERP_Helper_Data::LOG, $this->_debug);
    }

    protected function message ($text)
    {
        Mage::log ($text, null, EasySoftware_ERP_Helper_Data::LOG, $this->_debug);
    }

    protected function _fault ($code, $message = null)
    {
        throw new Exception ($message, 6666);
    }
}

