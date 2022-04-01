<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Bot_Model_Queue extends Mage_Core_Model_Abstract
{
    protected function _construct ()
    {
        $this->_init ('bot/queue');
    }

    protected function _beforeDelete ()
    {
        parent::_beforeDelete ();

        $collection = Mage::getModel ('bot/message')->getCollection ()
            ->addFieldToFilter ('queue_id', array ('eq' => $this->getId ()))
        ;

        foreach ($collection as $message)
        {
            $message->delete ();
        }
    }
}

