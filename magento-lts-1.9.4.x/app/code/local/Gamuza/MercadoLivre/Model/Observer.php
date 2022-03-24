<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Model_Observer
{
    public function catalogCategoryCollectionLoadBefore ($observer)
    {
        $collection = $observer->getEvent ()->getCategoryCollection ();

        if (!$collection->hasFlag ('mercadolivre'))
        {
            $collection->addAttributeToFilter ('name', array ('neq' => 'MercadoLivre'));

            $observer->getEvent ()->setCategoryCollection ($collection);
        }
    }
}

