<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Comanda_Block_Adminhtml_Mesa_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm ()
	{
		$form = new Varien_Data_Form ();
		$this->setForm ($form);

		$fieldset = $form->addFieldset ('comanda_form', array ('legend' => Mage::helper ('comanda')->__('Mesa Information')));

		$fieldset->addField ('name', 'text', array(
		    'label'    => Mage::helper ('comanda')->__('Name'),
		    'class'    => 'required-entry',
		    'name'     => 'name',
		    'required' => true,
		));
		$fieldset->addField ('description', 'text', array(
		    'label'    => Mage::helper ('comanda')->__('Description'),
		    'name'     => 'description',
		));
		$fieldset->addField ('is_active', 'select', array(
	        'label'    => Mage::helper ('comanda')->__('Is Active'),
	        'class'    => 'required-entry validate-select',
	        'name'     => 'is_active',
	        'required' => true,
            'options'  => Mage::getModel ('adminhtml/system_config_source_yesno')->toArray (),
		));

		if (Mage::getSingleton ('adminhtml/session')->getMesaData ())
		{
			$form->setValues (Mage::getSingleton ('adminhtml/session')->getMesaData ());

			Mage::getSingleton ('adminhtml/session')->setMesaData (null);
		}
		else if (Mage::registry ('mesa_data'))
        {
		    $form->setValues (Mage::registry ('mesa_data')->getData ());
		}

		return parent::_prepareForm();
	}
}

