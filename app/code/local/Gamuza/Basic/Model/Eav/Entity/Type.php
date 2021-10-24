<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2021 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Entity type model
 */
class Gamuza_Basic_Model_Eav_Entity_Type extends Mage_Eav_Model_Entity_Type
{
    /**
     * Retreive new incrementId
     *
     * @param int $storeId
     * @return string
     * @throws Exception
     */
    public function fetchNewIncrementId ($storeId = null)
    {
        $result = parent::fetchNewIncrementId ($storeId);

        $suffix = null;

        if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_ADMIN;
        }
        else if ($storeId == Mage_Core_Model_App::DISTRO_STORE_ID)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_MARKET;
        }
        else
        {
            $isMobile = Mage::helper ('basic')->isMobile ();

            $orderId = Mage::app ()->getRequest ()->getParam ('order_id');

            $currentOrder = Mage::getModel ('sales/order')->load ($orderId);

            $isApp = $currentOrder && $currentOrder->getData (Gamuza_Basic_Helper_Data::ORDER_ATTRIBUTE_IS_APP);

            $isBot = Mage::helper ('core')->isModuleEnabled ('Gamuza_Bot')
                && !strcmp (Mage::app ()->getRequest ()->getControllerModule (), 'Gamuza_Bot')
            ;

            if ($isMobile || $isApp)
            {
                $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_APP;
            }
            else if ($isBot)
            {
                $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_BOT;
            }
            else
            {
                $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_STORE;
            }
        }

        return sprintf ('%s-%s', $result, $suffix);
    }
}

