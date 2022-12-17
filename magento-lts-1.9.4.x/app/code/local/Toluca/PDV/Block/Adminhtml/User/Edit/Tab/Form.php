<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_User_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm ()
	{
		$form = new Varien_Data_Form ();
		$this->setForm ($form);

		$fieldset = $form->addFieldset ('pdv_form', array ('legend' => Mage::helper ('pdv')->__('User Information')));

		$fieldset->addField ('item_id', 'select', array(
	        'label'    => Mage::helper ('pdv')->__('Cashier'),
	        'class'    => 'required-entry validate-select',
	        'name'     => 'item_id',
	        'required' => true,
            'options'  => Toluca_PDV_Block_Adminhtml_User_Grid::getItems (),
		));
		$fieldset->addField ('name', 'text', array(
		    'label'    => Mage::helper ('pdv')->__('Name'),
		    'class'    => 'required-entry',
		    'name'     => 'name',
		    'required' => true,
		));
		$fieldset->addField ('password', 'password', array(
	        'label'    => Mage::helper ('pdv')->__('Password'),
	        'class'    => 'required-entry',
	        'name'     => 'password',
	        'required' => true,
		));
		$fieldset->addField ('is_active', 'select', array(
	        'label'    => Mage::helper ('pdv')->__('Is Active'),
	        'class'    => 'required-entry validate-select',
	        'name'     => 'is_active',
	        'required' => true,
            'options'  => Mage::getModel ('adminhtml/system_config_source_yesno')->toArray (),
		));

		if (Mage::getSingleton ('adminhtml/session')->getUserData ())
		{
			$form->setValues (Mage::getSingleton ('adminhtml/session')->getUserData ());

			Mage::getSingleton ('adminhtml/session')->setUserData (null);
		}
		else if (Mage::registry ('user_data'))
        {
		    $form->setValues (Mage::registry ('user_data')->getData ());
		}

        $form->getElement ('password')->setValue ('');

		return parent::_prepareForm();
	}
}

