<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_Item_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct ()
	{
		parent::__construct ();

		$this->_blockGroup = 'pdv';
		$this->_controller = 'adminhtml_item';
		$this->_objectId   = 'entity_id';

		$this->_updateButton ('save', 'label', Mage::helper ('pdv')->__('Save Cashier'));
        $this->_removeButton ('delete');

		$this->_addButton ('saveandcontinue', array(
			'label'   => Mage::helper ('pdv')->__('Save and Continue Edit'),
			'onclick' => 'saveAndContinueEdit ()',
			'class'   => 'save',
		), -100);

		$this->_formScripts [] = "
			function saveAndContinueEdit () {
				editForm.submit ($('edit_form').action + 'back/edit/');
			}
		";
	}

	public function getHeaderText ()
	{
        $item = Mage::registry ('item_data');

		if ($item && $item->getId ())
        {
		    return Mage::helper ('pdv')->__("Edit Cashier '%s'", $this->htmlEscape ($item->getId ()));
		}
		else
        {
		     return Mage::helper ('pdv')->__('Add New Cashier');
		}
	}
}
