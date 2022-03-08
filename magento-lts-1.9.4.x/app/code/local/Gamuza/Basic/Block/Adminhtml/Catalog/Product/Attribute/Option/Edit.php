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
	
class Gamuza_Basic_Block_Adminhtml_Catalog_Product_Attribute_Option_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct ()
	{
		parent::__construct ();

		$this->_blockGroup = 'basic';
		$this->_controller = 'adminhtml_catalog_product_option';
		$this->_objectId   = 'entity_id';

		$this->_updateButton ('save',   'label', Mage::helper ('basic')->__('Save Option'));
		$this->_updateButton ('delete', 'label', Mage::helper ('basic')->__('Delete Option'));

		$this->_addButton ('saveandcontinue', array(
			'label'   => Mage::helper ('basic')->__('Save and Continue Edit'),
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
        $attributeOption = Mage::registry ('attribute_option_data');

		if ($attributeOption && $attributeOption->getId ())
        {
		    return Mage::helper ('basic')->__("Edit Attribute Option '%s'", $this->htmlEscape ($attributeOption->getId ()));
		} 
		else
        {
		     return Mage::helper ('basic')->__('Add New Option');
		}
	}
}

