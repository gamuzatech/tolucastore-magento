<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Bot module observer
 */
class Gamuza_Bot_Model_Observer
{
    const BOT_QUEUE_LIFETIME = 86400;

    public function cleanExpiredQueues()
    {
        /** @var $chats Gamuza_Bot_Model_Mysql4_Queue_Collection */
        $queues = Mage::getModel('bot/queue')->getCollection()
            ->addFieldToFilter('status', array ('neq' => Gamuza_Bot_Helper_Data::STATUS_ORDER))
        ;

        $queues->addFieldToFilter('updated_at', array('to'=>date("Y-m-d H:i:s", mktime(23, 59, 59) - self::BOT_QUEUE_LIFETIME)));

        $queues->walk('delete');

        return $this;
    }
}

