<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$installer = $this;
$installer->startSetup ();

function addMercadoLivreRootCategory ($installer, $name)
{
    $collection = Mage::getModel ('catalog/category')->getCollection ()
        ->addRootLevelFilter ()
        ->setStoreId (0)
        ->setProductStoreId (0)
        ->addAttributeToFilter ('name', array ('eq' => $name))
    ;

    $mageCategory = $collection->getFirstItem ();

    if (!$mageCategory || !$mageCategory->getId ())
    {
        $parentCategory = Mage::getModel ('catalog/category')->load (Mage_Catalog_Model_Category::TREE_ROOT_ID);

        $mageCategory = Mage::getModel ('catalog/category')
            ->setParentId ($parentCategory->getId ())
            ->setPath ($parentCategory->getPath ())
            ->setDisplayModel (Mage_Catalog_Model_Category::DM_PRODUCT)
            ->setIsAnchor (1)
        ;
    }

    $mageCategory->setStoreId (0)
        ->setIsActive (1)
        ->setName ($name)
        ->save ()
    ;

    Mage::getModel ('core/config')
        ->saveConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ROOT_CATEGORY_ID, $mageCategory->getId ())
    ;
}

addMercadoLivreRootCategory ($installer, 'MercadoLivre');

$installer->endSetup ();

