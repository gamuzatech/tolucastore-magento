<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_PDV_Block_Adminhtml_User extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct ()
	{
	    $this->_blockGroup = 'pdv';
	    $this->_controller = 'adminhtml_user';

	    $this->_headerText = Mage::helper ('pdv')->__('Users Manager');
        $this->_addButtonLabel = Mage::helper ('pdv')->__('Add New User');

	    parent::__construct();
	}
}

