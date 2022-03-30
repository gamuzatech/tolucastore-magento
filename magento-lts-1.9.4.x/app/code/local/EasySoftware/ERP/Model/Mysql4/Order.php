<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Mysql4_Order extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct ()
    {
        $this->_init ('erp/order', 'entity_id');
    }
}

