<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Api model
 */
class Gamuza_Basic_Model_Api_User extends Mage_Api_Model_User
{
    /**
     * @inheritDoc
     */
    protected function _afterSave()
    {
        $result = parent::_afterSave();

        if (!strcmp ($this->getUsername (), Gamuza_Basic_Helper_Data::DEFAULT_API_USER))
        {
            $this->setData('is_system', 1);
        }

        $this->_getResource()->save($this);

        return $result;
    }
}

