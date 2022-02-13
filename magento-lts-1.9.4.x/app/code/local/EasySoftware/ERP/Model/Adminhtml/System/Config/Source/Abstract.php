<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

abstract class EasySoftware_ERP_Model_Adminhtml_System_Config_Source_Abstract
{
    protected $_entityType = '';

    public function getEntityTypeId ()
    {
        return Mage::helper ('erp')->getEntityTypeId ($this->_entityType);
    }

    public function getAttributeSetCollection ()
    {
        $collection = Mage::getResourceModel ('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter ($this->getEntityTypeId ())
        ;

        return $collection;
    }
}

