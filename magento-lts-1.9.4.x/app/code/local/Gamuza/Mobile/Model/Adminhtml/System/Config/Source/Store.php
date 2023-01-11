<?php
/**
 * @package     Gamuza_Mobile
 * @copyright   Copyright (c) 2023 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Mobile_Model_Adminhtml_System_Config_Source_Store
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options)
        {
            $this->_options = Mage::getResourceModel('core/store_collection')
                ->setLoadDefault(true)
                ->load()
                ->toOptionArray();
        }

        return $this->_options;
    }
}

