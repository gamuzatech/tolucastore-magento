<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
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
 * These files are distributed with gamuza_bot-magento at http://github.com/gamuzatech/.
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

