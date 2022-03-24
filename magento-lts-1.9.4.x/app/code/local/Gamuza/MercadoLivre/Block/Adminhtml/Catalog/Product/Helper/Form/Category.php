<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Block_Adminhtml_Catalog_Product_Helper_Form_Category
    extends Varien_Data_Form_Element_Hidden
{
    public function getAfterElementHtml ()
    {
        $afterHtml = Mage::app ()->getLayout ()
            ->createBlock ('core/template')
            ->setElement ($this)
            ->setTemplate ('gamuza/mercadolivre/catalog/product/helper/category.phtml')
            ->toHtml ()
        ;

        return $afterHtml;
    }

    public function getIsActive ()
    {
        return Mage::getStoreConfigFlag (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ACTIVE);
    }

    public function getCategories ()
    {
        $rootCategoryId = Mage::getStoreConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ROOT_CATEGORY_ID);
        $rootCategory   = Mage::getModel ('catalog/category')->load ($rootCategoryId);

        if (!$rootCategory || !$rootCategory->getId ())
        {
            return array ();
        }

        $collection = Mage::getModel ('catalog/category')->getCollection ()
            ->addAttributeToFilter ('path', array (
                'like' => sprintf ('%s/%s/%%', Mage_Catalog_Model_Category::TREE_ROOT_ID, $rootCategoryId)
            ))
            ->addAttributeToSelect (Gamuza_MercadoLivre_Helper_Data::CATEGORY_ATTRIBUTE_ID)
            ->addNameToResult ()
            ->setFlag ('mercadolivre', true)
        ;

        return $collection;
    }

    public function getCategoryName ()
    {
        $product = Mage::registry ('current_product');

        if (!$product || !$product->getId ())
        {
            return null;
        }

        $category = $product->getData (Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_CATEGORY);

        if (!empty ($category))
        {
            $category = json_decode ($category);

            return sprintf ('%s ( %s )', $category [1], $category [0]);
        }
    }
}

