<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Adminhtml_UserController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('toluca/pdv/user');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()->_setActiveMenu ('pdv/user')
            ->_addBreadcrumb(
                Mage::helper ('pdv')->__('Users Manager'),
                Mage::helper ('pdv')->__('Users Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('PDV'));
	    $this->_title ($this->__('Users Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}

	public function newAction ()
	{
	    $this->_title ($this->__('PDV'));
	    $this->_title ($this->__('Users Manager'));
	    $this->_title ($this->__('New User'));

        $id = $this->getRequest ()->getParam ('id');

	    $model = Mage::getModel ('pdv/user')->load ($id);

	    $userData = Mage::getSingleton ('adminhtml/session')->getUserData (true);

	    if (!empty ($userData))
        {
		    $model->setData ($userData);
	    }

	    Mage::register ('user_data', $model);

        $this->_initAction ();

	    $this->_addContent ($this->getLayout ()->createBlock ('pdv/adminhtml_user_edit'));
        $this->_addLeft ($this->getLayout ()->createBlock ('pdv/adminhtml_user_edit_tabs'));

	    $this->renderLayout ();
	}

	public function editAction ()
	{
	    $this->_title ($this->__('PDV'));
		$this->_title ($this->__('User'));
	    $this->_title ($this->__('Edit User'));

		$id = $this->getRequest()->getParam ('id');

		$model = Mage::getModel ('pdv/user')->load ($id);

		if ($model && $model->getId ())
        {
			Mage::register ('user_data', $model);

            $this->_initAction ();

			$this->_addContent ($this->getLayout ()->createBlock ('pdv/adminhtml_user_edit'));
            $this->_addLeft ($this->getLayout()->createBlock ('pdv/adminhtml_user_edit_tabs'));

			$this->renderLayout();
		}
		else
        {
			Mage::getSingleton ('adminhtml/session')->addError (Mage::helper ('pdv')->__('User does not exist.'));

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

                $postData ['password'] = Mage::helper ('core')->getHash ($postData ['password'], true);

				$model = Mage::getModel ('pdv/user')
				    ->addData ($postData)
                    ->setData ($id ? 'updated_at' : 'created_at', date ('c'))
				    ->setId ($id)
				    ->save ()
                ;

				Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('pdv')->__('User was successfully saved.'));
				Mage::getSingleton ('adminhtml/session')->setUserData (false);

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
				Mage::getSingleton ('adminhtml/session')->setUserData ($this->getRequest ()->getPost ());

				$this->_redirect ('*/*/edit', array ('id' => $this->getRequest ()->getParam ('id')));

			    return $this;
			}
		}

		$this->_redirect ('*/*/index');
	}
}

