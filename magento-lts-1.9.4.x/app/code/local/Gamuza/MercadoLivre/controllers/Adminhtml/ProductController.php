<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Adminhtml_ProductController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed ()
    {
        return Mage::getSingleton ('admin/session')->isAllowed ('gamuza/mercadolivre/product');
    }

    protected function _initAction ()
    {
        $this->loadLayout ()
            ->_setActiveMenu ('gamuza/mercadolivre/product')
            ->_addBreadcrumb (
                Mage::helper ('adminhtml')->__('Products Manager'),
                Mage::helper('adminhtml')->__('Products Manager')
            )
        ;

        return $this;
    }

    public function indexAction ()
    {
        $this->_title ($this->__('MercadoLivre'));
        $this->_title ($this->__('Manage Products'));

        $this->_initAction ();

        $this->renderLayout ();
    }

    public function massRemoveAction ()
    {
        try
        {
            $ids = $this->getRequest ()->getPost ('entity_ids', array ());

            foreach ($ids as $id)
            {
                $model = Mage::getModel ('mercadolivre/product')
                    ->setId ($id)
                    ->delete ()
                ;
            }

            Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('adminhtml')->__('Items were successfully removed.'));
        }
        catch (Exception $e)
        {
            Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());
        }

        $this->_redirect ('*/*/index');
    }
}

