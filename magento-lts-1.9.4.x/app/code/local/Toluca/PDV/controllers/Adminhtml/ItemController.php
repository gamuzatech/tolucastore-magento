<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Adminhtml_ItemController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('toluca/pdv/item');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()->_setActiveMenu ('pdv/item')
            ->_addBreadcrumb(
                Mage::helper ('pdv')->__('Cashiers Manager'),
                Mage::helper ('pdv')->__('Cashiers Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('PDV'));
	    $this->_title ($this->__('Cashiers Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}

	public function newAction ()
	{
	    $this->_title ($this->__('PDV'));
	    $this->_title ($this->__('Cashiers Manager'));
	    $this->_title ($this->__('New Item'));

        $id = $this->getRequest ()->getParam ('id');

	    $model = Mage::getModel ('pdv/item')->load ($id);

	    $itemData = Mage::getSingleton ('adminhtml/session')->getItemData (true);

	    if (!empty ($itemData))
        {
		    $model->setData ($itemData);
	    }

	    Mage::register ('item_data', $model);

        $this->_initAction ();

	    $this->_addContent ($this->getLayout ()->createBlock ('pdv/adminhtml_item_edit'));
        $this->_addLeft ($this->getLayout ()->createBlock ('pdv/adminhtml_item_edit_tabs'));

	    $this->renderLayout ();
	}

	public function editAction ()
	{
	    $this->_title ($this->__('PDV'));
		$this->_title ($this->__('Item'));
	    $this->_title ($this->__('Edit Item'));

		$id = $this->getRequest()->getParam ('id');

		$model = Mage::getModel ('pdv/item')->load ($id);

		if ($model && $model->getId ())
        {
			Mage::register ('item_data', $model);

            $this->_initAction ();

			$this->_addContent ($this->getLayout ()->createBlock ('pdv/adminhtml_item_edit'));
            $this->_addLeft ($this->getLayout()->createBlock ('pdv/adminhtml_item_edit_tabs'));

			$this->renderLayout();
		}
		else
        {
			Mage::getSingleton ('adminhtml/session')->addError (Mage::helper ('pdv')->__('Item does not exist.'));

			$this->_redirect ('*/*/index');
		}
	}

	public function saveAction()
	{
		$postData = $this->getRequest ()->getPost ();

		if ($postData)
        {
			try
            {
                $id = $this->getRequest ()->getParam ('id');

				$model = Mage::getModel ('pdv/item')
				    ->addData ($postData)
                    ->setData ($id ? 'updated_at' : 'created_at', date ('c'))
				    ->setId ($id)
				    ->save ()
                ;

                $code = hash ('crc32', $model->getId ());

                $model->setCode ($code)->save ();

				Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('pdv')->__('Cashier was successfully saved.'));
				Mage::getSingleton ('adminhtml/session')->setItemData (false);

				if ($this->getRequest()->getParam ('back'))
                {
					$this->_redirect ('*/*/edit', array ('id' => $model->getId ()));

					return $this;
				}

				$this->_redirect ('*/*/index');

				return $this;
			}
			catch (Exception $e)
            {
				Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());
				Mage::getSingleton ('adminhtml/session')->setItemData ($this->getRequest ()->getPost ());

				$this->_redirect ('*/*/edit', array ('id' => $this->getRequest ()->getParam ('id')));

			    return $this;
			}
		}

		$this->_redirect ('*/*/index');
	}
}

