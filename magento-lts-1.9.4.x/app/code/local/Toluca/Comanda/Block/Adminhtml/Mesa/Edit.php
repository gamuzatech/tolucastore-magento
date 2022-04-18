<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Comanda_Block_Adminhtml_Mesa_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct ()
	{
		parent::__construct ();

		$this->_blockGroup = 'comanda';
		$this->_controller = 'adminhtml_mesa';
		$this->_objectId   = 'entity_id';

		$this->_updateButton ('save',   'label', Mage::helper ('comanda')->__('Save Mesa'));
		$this->_updateButton ('delete', 'label', Mage::helper ('comanda')->__('Delete Mesa'));

		$this->_addButton ('saveandcontinue', array(
			'label'   => Mage::helper ('comanda')->__('Save and Continue Edit'),
			'onclick' => 'saveAndContinueEdit ()',
			'class'   => 'save',
		), -100);

		$this->_formScripts [] = "
			function saveAndContinueEdit () {
				editForm.submit ($('edit_form').action + 'back/edit/');
			}
		";

        $id = $this->getRequest ()->getParam ('id');

        if ($id > 0)
        {
            $collection = Mage::getModel ('comanda/item')->getCollection ()
                ->addFieldToFilter ('mesa_id', array ('eq' => $id))
            ;

            if ($collection->getSize () > 0)
            {
                $this->_removeButton ('delete');
            }
        }
	}

	public function getHeaderText ()
	{
        $mesa = Mage::registry ('mesa_data');

		if ($mesa && $mesa->getId ())
        {
		    return Mage::helper ('comanda')->__("Edit Mesa '%s'", $this->htmlEscape ($mesa->getId ()));
		}
		else
        {
		     return Mage::helper ('comanda')->__('Add New Mesa');
		}
	}

    public function getSendMessage ()
    {
        return Mage::helper ('comanda')->__('Confirm sending the mesa to the queue?');
    }

    public function getSendUrl ()
    {
        return $this->getUrl ('*/*/queue', array ('id' => $this->getRequest()->getParam('id')));
    }
}

