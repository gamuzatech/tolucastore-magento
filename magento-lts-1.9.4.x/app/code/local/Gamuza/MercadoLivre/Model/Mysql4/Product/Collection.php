<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Model_Mysql4_Product_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct ()
    {
        $this->_init ('mercadolivre/product');
    }
}

