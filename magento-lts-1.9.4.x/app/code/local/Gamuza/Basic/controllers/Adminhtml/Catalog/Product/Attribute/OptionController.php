<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

/**
 * See the AUTHORS file for a list of people on the Gamuza Team.
 * See the ChangeLog files for a list of changes.
 * These files are distributed with gamuza_basic-magento at http://github.com/gamuzatech/.
 */

class Gamuza_Basic_Adminhtml_Catalog_Product_Attribute_OptionController
    extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('catalog/attributes/options');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()->_setActiveMenu ('catalog/attributes/options')
            ->_addBreadcrumb (
                Mage::helper ('basic')->__('Attribute Options Manager'),
                Mage::helper ('basic')->__('Attribute Options Manager')
            )
        ;

	    $this->getLayout ()->getBlock ('head')->setCanLoadExtJs (true);

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('basic'));
	    $this->_title ($this->__('Manage Attribute Options'));

		$this->_initAction ();

		$this->renderLayout ();
	}

	public function newAction ()
	{
	    $this->_title ($this->__('basic'));
	    $this->_title ($this->__('Manage Attribute Options'));
	    $this->_title ($this->__('New Option'));

        $id = $this->getRequest ()->getParam ('id');

	    $model = Mage::getModel ('basic/eav_entity_attribute_option')->load ($id);

	    $attributeOptionsData = Mage::getSingleton ('adminhtml/session')->getAttributeOptionData (true);

	    if (!empty ($attributeOptionsData))
        {
		    $model->setData ($attributeOptionsData);
	    }

	    Mage::register ('attribute_option_data', $model);

        $this->_initAction ();

	    $this->_addContent ($this->getLayout ()->createBlock ('basic/adminhtml_catalog_product_attribute_option_edit'));
        $this->_addLeft ($this->getLayout ()->createBlock ('basic/adminhtml_catalog_product_attribute_option_edit_tabs'));

	    $this->renderLayout ();
	}

	public function editAction ()
	{
	    $this->_title ($this->__('basic'));
		$this->_title ($this->__('Manage Attribute Options'));
	    $this->_title ($this->__('Edit Option'));

		$id = $this->getRequest()->getParam ('id');

		$model = Mage::getModel ('basic/eav_entity_attribute_option')->load ($id);

		if ($model->getId ())
        {
			Mage::register ('attribute_option_data', $model);

            $this->_initAction ();

			$this->_addContent ($this->getLayout ()->createBlock ('basic/adminhtml_catalog_product_attribute_option_edit'));
            $this->_addLeft ($this->getLayout()->createBlock ('basic/adminhtml_catalog_product_attribute_option_edit_tabs'));

			$this->renderLayout();
		}
		else
        {
			Mage::getSingleton ('adminhtml/session')->addError (Mage::helper ('basic')->__('Attribute Option does not exist.'));

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

				$model = Mage::getModel ('basic/eav_entity_attribute_option')
				    ->addData ($postData)
				    ->setId ($id)
				    ->save ()
                ;

				Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('basic')->__('Attribute Option was successfully saved.'));
				Mage::getSingleton ('adminhtml/session')->setAttributeOptionData (false);

				if ($this->getRequest()->getParam ('back'))
                {
					$this->_redirect ('*/*/edit', array ('id' => $model->getId ()));

					return $this;
				}

				$this->_redirect ('*/*/');

				return $this;
			}
			catch (Exception $e)
            {
				Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());
				Mage::getSingleton ('adminhtml/session')->setAttributeOptionData ($this->getRequest ()->getPost ());

				$this->_redirect ('*/*/edit', array ('id' => $this->getRequest ()->getParam ('id')));

			    return $this;
			}
		}

		$this->_redirect ('*/*/');
	}

	public function deleteAction ()
	{
        $value = Mage::getModel ('basic/eav_entity_attribute_option_value')->load (629);

		if ($this->getRequest ()->getParam ('id') > 0 )
        {
			try
            {
				$model = Mage::getModel ('basic/eav_entity_attribute_option');
				$model->setId ($this->getRequest ()->getParam ('id'))->delete ();

				Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('basic')->__('Attribute Option was successfully deleted.'));

				$this->_redirect ('*/*/');
			}
			catch (Exception $e)
            {
				Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());

				$this->_redirect ('*/*/edit', array ('id' => $this->getRequest ()->getParam ('id')));
			}
		}

		$this->_redirect ('*/*/');
	}
}

