<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2017 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Basic module observer
 */
class Gamuza_Basic_Model_Observer
{
    const SALES_QUOTE_LIFETIME = 86400;

    public function adminhtmlCmsPageEditTabContentPrepareForm ($observer)
    {
        $event = $observer->getEvent ();
        $form  = $event->getForm ();

        $form->getElement ('content')->setRequired (false);
    }

    public function adminhtmlControllerActionPredispatchStart ($observer)
    {
        Mage::getDesign ()->setArea ('adminhtml')->setTheme ('zzz'); // use fallback theme
    }

    public function catalogProductSaveBefore ($observer)
    {
        $event   = $observer->getEvent ();
        $product = $event->getProduct ();

        {
            $prefix = time ();
            $token  = hash ('sha512', uniqid (rand (), true));

            $product->setSku ($prefix . '_' . $token);
            $product->setUrlKey ($prefix . '-' . $token);
        }

        return $this;
    }

    public function catalogCategorySaveAfter ($observer)
    {
        $event    = $observer->getEvent ();
        $category = $event->getCategory ();

        {
            $resource = Mage::getSingleton ('core/resource');
            $write    = $resource->getConnection ('core_write');

            /**
             * SKU
             */
            $table = $resource->getTableName ('catalog/category');

            $token = hash ('crc32', $category->getId ());

            $query = sprintf ("UPDATE {$table} SET sku = '{$token}' WHERE entity_id = %s LIMIT 1",
                $category->getId ()
            );

            $write->query ($query);
        }

        return $this;
    }

    public function catalogProductSaveAfter ($observer)
    {
        $event   = $observer->getEvent ();
        $product = $event->getProduct ();

        {
            $resource = Mage::getSingleton ('core/resource');
            $write    = $resource->getConnection ('core_write');

            /**
             * SKU
             */
            $table = $resource->getTableName ('catalog/product');

            $token = hash ('crc32', $product->getId ());

            $query = sprintf ("UPDATE {$table} SET sku = '{$token}' WHERE entity_id = %s LIMIT 1",
                $product->getId ()
            );

            $write->query ($query);

            /**
             * URL Key
             */
            $attribute = Mage::getSingleton ('eav/config')->getAttribute (Mage_Catalog_Model_Product::ENTITY , 'url_key');

            $table = $resource->getTableName ('catalog_product_entity_' . $attribute->getBackendType ());

            $query = sprintf ("UPDATE {$table} SET value = '{$token}' WHERE attribute_id = %s AND entity_id = %s",
                $attribute->getId (), $product->getId ()
            );

            $write->query ($query);
        }

        $mediaDir = Mage::getBaseDir (Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $mediaGallery = $product->getMediaGallery ();

        foreach ($mediaGallery ['images'] as $image)
        {
            $file = $mediaDir . DS . 'catalog' . DS . 'product' . DS . $image ['file'];

            if (!file_exists ($file)) continue;

            $image = new Varien_Image ($file);

            $image->backgroundColor (array (255, 255, 255));
            $image->save ($file);

            if ($image->getOriginalWidth () > 1024)
            {
                $original = imagecreatefromstring (file_get_contents ($file));

                $resized = imagecreatetruecolor (1024, 1024);

                imagecopyresampled ($resized, $original, 0, 0, 0, 0, 1024, 1024, $image->getOriginalWidth (), $image->getOriginalHeight ());

                imagejpeg ($resized, $file, 75);
            }
        }

        return $this;
    }

    public function cleanExpiredQuotes()
    {
        Mage::dispatchEvent('clear_expired_quotes_before', array('sales_observer' => $this));

        /** @var $quotes Mage_Sales_Model_Mysql4_Quote_Collection */
        $quotes = Mage::getModel('sales/quote')->getCollection()
            ->addFieldToFilter('is_app', array ('neq' => true))
            ->addFieldToFilter('is_bot', array ('neq' => true))
            ->addFieldToFilter('is_pdv', array ('neq' => true))
        ;

        $quotes->addFieldToFilter('updated_at', array('to'=>date("Y-m-d H:i:s", mktime(23, 59, 59) - self::SALES_QUOTE_LIFETIME)));

        $quotes->walk('delete');

        return $this;
    }
}

