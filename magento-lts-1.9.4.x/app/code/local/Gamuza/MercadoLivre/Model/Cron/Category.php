<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Model_Cron_Category extends Gamuza_MercadoLivre_Model_Cron_Abstract
{
    const CATEGORIES_INFO_METHOD  = 'categories/{categoryId}';

    public function run ()
    {
        if (!$this->getStoreConfig ('active'))
        {
            return $this;
        }

        $rootCategoryId = $this->getStoreConfig ('root_category_id');
        $rootCategory   = Mage::getModel ('catalog/category')->load ($rootCategoryId);

        if (!$rootCategory || !$rootCategory->getId ())
        {
            throw Mage::exception ('Gamuza_MercadoLivre', $this->getHelper ()->__('Root category ID %s was not found!', $rootCategoryId));
        }

        try
        {
            $result = $this->getHelper ()->api (Gamuza_MercadoLivre_Helper_Data::API_SITES_CATEGORIES_URL);

            if (empty ($result))
            {
                throw Mage::exception ('Gamuza_MercadoLivre', $this->getHelper ()->__('Empty categories information!'));
            }

            foreach ($result as $item)
            {
                $this->_updateCategory ($item, $rootCategory);
            }
        }
        catch (Exception $e)
        {
            throw Mage::exception ('Gamuza_MercadoLivre', $e->getMessage (), $e->getCode ());
        }
    }

    private function _updateCategory ($item, $rootCategory)
    {
        $category = Mage::getModel ('catalog/category')->loadByAttribute (Gamuza_MercadoLivre_Helper_Data::CATEGORY_ATTRIBUTE_ID, $item->id);

        if (!$category || !$category->getId ())
        {
            $category = Mage::getModel ('catalog/category')
                ->setParentId ($rootCategory->getId ())
                ->setPath ($rootCategory->getPath ())
                ->setData (Gamuza_MercadoLivre_Helper_Data::CATEGORY_ATTRIBUTE_ID, $item->id)
                ->setDisplayModel (Mage_Catalog_Model_Category::DM_PRODUCT)
                ->setIsAnchor (1)
            ;
        }

        $category->setStoreId (0)
            ->setIsActive (true)
            ->setName ($item->name)
            ->save ()
        ;

        try
        {
            $apiUrl = $this->getStoreConfig ('api_url');

            $categoriesInfoMethod = str_replace ('{categoryId}', $item->id, self::CATEGORIES_INFO_METHOD);

            $categoriesInfoResult = $this->getHelper ()->api ($apiUrl . '/' . $categoriesInfoMethod);

            if (empty ($categoriesInfoResult))
            {
                throw Mage::exception ('Gamuza_MercadoLivre', $this->getHelper ()->__('Empty category information!'));
            }
/*
            foreach ($categoriesInfoResult->children_categories as $item)
            {
                $this->_updateCategory ($item, $category);
            }
*/
        }
        catch (Exception $e)
        {
            throw Mage::exception ('Gamuza_MercadoLivre', $e->getMessage (), $e->getCode ());
        }
    }
}

