<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Model_Operator extends Mage_Core_Model_Abstract
{
    protected function _construct ()
    {
        $this->_init ('pdv/operator');
    }

    public function getItemStatus ()
    {
        if ($this->getId () && $this->getItemId ())
        {
            $item = Mage::getModel ('pdv/item')->load ($this->getItemId ());

            if ($item && $item->getId ())
            {
                return $item->getStatus ();
            }
        }
    }
}

