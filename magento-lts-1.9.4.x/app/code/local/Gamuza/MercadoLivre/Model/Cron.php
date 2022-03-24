<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Model_Cron
{
    public function runToken ()
    {
        Mage::getModel ('mercadolivre/cron_token')->refresh ();
    }

    public function runCategory ()
    {
        Mage::getModel ('mercadolivre/cron_category')->run ();
    }

    public function runProduct ()
    {
        Mage::getModel ('mercadolivre/cron_product')->run ();
    }
}

