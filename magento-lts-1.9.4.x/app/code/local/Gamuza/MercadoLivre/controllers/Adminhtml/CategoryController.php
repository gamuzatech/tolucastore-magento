<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_Action
{
    const CATEGORIES_INFO_METHOD = 'categories/{categoryId}';

    protected function _isAllowed ()
    {
        return Mage::getSingleton ('admin/session')->isAllowed ('gamuza/mercadolivre');
    }

    public function indexAction ()
    {
        $id = $this->getRequest ()->getParam ('id');

        $active = Mage::getStoreConfigFlag (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_ACTIVE);

        $result = array ();

        if (!empty ($id) && $active)
        {
            $apiUrl = Mage::getStoreConfig (Gamuza_MercadoLivre_Helper_Data::XML_PATH_MERCADOLIVRE_SETTINGS_API_URL);

            $categoriesInfoMethod = str_replace ('{categoryId}', $id, self::CATEGORIES_INFO_METHOD);

            $categoriesInfoResult = Mage::helper ('mercadolivre')->api ($apiUrl . '/' . $categoriesInfoMethod);

            if (!empty ($categoriesInfoResult))
            {
                foreach ($categoriesInfoResult->children_categories as $item)
                {
                    $result [$item->id] = $item->name;
                }
            }
        }

        $this->getResponse ()
            ->setHeader ('Content-Type', 'application/json')
            ->setBody (json_encode ($result))
        ;
    }

    public function updateAction ()
    {
        Mage::getModel ('mercadolivre/cron')->runCategory ();
    }
}

