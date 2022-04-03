<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Bot_Block_Adminhtml_Promotion_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct ()
	{
		parent::__construct ();

		$this->_blockGroup = 'bot';
		$this->_controller = 'adminhtml_promotion';
		$this->_objectId   = 'entity_id';

		$this->_updateButton ('save',   'label', Mage::helper ('bot')->__('Save Promotion'));
		$this->_updateButton ('delete', 'label', Mage::helper ('bot')->__('Delete Promotion'));

		$this->_addButton ('saveandcontinue', array(
			'label'   => Mage::helper ('bot')->__('Save and Continue Edit'),
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
        $promotion = Mage::registry ('promotion_data');

		if ($promotion && $promotion->getId ())
        {
		    return Mage::helper ('bot')->__("Edit Promotion '%s'", $this->htmlEscape ($promotion->getId ()));
		}
		else
        {
		     return Mage::helper ('bot')->__('Add New Promotion');
		}
	}
}

