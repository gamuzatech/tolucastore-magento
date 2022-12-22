<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Admin user model
 */
class Gamuza_Basic_Model_Admin_User extends Mage_Admin_Model_User
{
    /**
     * @inheritDoc
     */
    protected function _beforeSave()
    {
        $result = parent::_beforeSave();

        if (!strcmp ($this->getUsername (), Gamuza_Basic_Helper_Data::DEFAULT_ADMIN_USER))
        {
            $this->setData('is_system', 1);
        }

        return $result;
    }
}

