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
    const API_METHOD_BOT_CHAT_MESSAGE = 'bot_chat.message';

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

        $quote = $this->getQuote();

        if (!$quote || !$quote->getId ())
        {
            return $result;
        }

        $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_OTHER;

        if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_ADMIN;
        }
        else if ($storeId == Mage_Core_Model_App::DISTRO_STORE_ID)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_STORE;
        }

        $isMobile = Mage::helper ('basic')->isMobile ();

        $isApp = $quote->getData (Gamuza_Basic_Helper_Data::ORDER_ATTRIBUTE_IS_APP);
        $isBot = $quote->getData (Gamuza_Basic_Helper_Data::ORDER_ATTRIBUTE_IS_BOT);
/*
        $isBot = Mage::helper ('core')->isModuleEnabled ('Toluca_Bot')
            && (!strcmp (Mage::app ()->getRequest ()->getControllerModule (), 'Toluca_Bot')
            xor (!strcmp (Mage::app ()->getRequest ()->getControllerModule (), 'Gamuza_JsonApi')
            && strpos (Mage::app ()->getRequest ()->getRawBody (), self::API_METHOD_BOT_CHAT_MESSAGE) !== false))
        ;
*/
        $isPdv = $quote->getData (Gamuza_Basic_Helper_Data::ORDER_ATTRIBUTE_IS_PDV);
        $isSat = $quote->getData (Gamuza_Basic_Helper_Data::ORDER_ATTRIBUTE_IS_SAT);

        if ($isMobile)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_MOBILE;
        }
        else if ($isApp)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_APP;
        }
        else if ($isBot)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_BOT;
        }
        else if ($isPdv)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_PDV;
        }
        else if ($isSat)
        {
            $suffix = Gamuza_Basic_Helper_Data::ORDER_SUFFIX_SAT;
        }

        return sprintf ('%s-%s', $result, $suffix);
    }
}

