<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Comanda_Block_Adminhtml_Mesa_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('mesa_tabs');
		$this->setDestElementId ('edit_form');
		$this->setTitle (Mage::helper ('comanda')->__('Mesa Information'));
	}

	protected function _beforeToHtml ()
	{
		$this->addTab ('form_section', array(
		    'label'   => Mage::helper ('comanda')->__('Mesa Information'),
		    'title'   => Mage::helper ('comanda')->__('Mesa Information'),
		    'content' => $this->getLayout ()->createBlock ('comanda/adminhtml_mesa_edit_tab_form')->toHtml (),
		));

		return parent::_beforeToHtml ();
	}
}

