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

        $treeCategoryId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        $rootCategoryId = Mage::getStoreConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ROOT_CATEGORY_ID);

        if (!$collection->hasFlag ('mercadolivre') && intval ($rootCategoryId) > 0)
        {
            $collection->getSelect ()
                ->where (sprintf ("NOT (path = '%s/%s' OR path LIKE '%s/%s/%%')",
                    $treeCategoryId, $rootCategoryId,
                    $treeCategoryId, $rootCategoryId,
                ))
            ;

            $observer->getEvent ()->setCategoryCollection ($collection);
        }
    }
}

