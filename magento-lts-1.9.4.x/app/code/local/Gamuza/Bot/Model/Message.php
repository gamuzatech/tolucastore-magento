<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Bot_Model_Message extends Mage_Core_Model_Abstract
{
    protected function _construct ()
    {
        $this->_init ('bot/message');
    }
}

