<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Adminhtml_System_Config_Source_Attribute
    extends EasySoftware_ERP_Model_Adminhtml_System_Config_Source_Abstract
{
    protected $_attributeField = 'attribute_id';

    public function getAttributeCollection ()
    {
        $collection = Mage::getResourceModel ('eav/entity_attribute_collection')
            ->setEntityTypeFilter ($this->getEntityTypeId ())
            ->addFieldToFilter ('is_visible', true)
        ;

        $collection->getSelect()->order('frontend_label');

        return $collection;
    }

    public function getAttributeSetCollection ()
    {
        $collection = Mage::getResourceModel ('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter ($this->getEntityTypeId ())
        ;

        return $collection;
    }

    public function getEntityTypeId ()
    {
        $item = Mage::getModel ($this->_entityType);

        return $item->getResource ()->getTypeId ();
    }

    public function toOptionArray ()
    {
        $result = null;

        $attributeCollection = $this->getAttributeCollection ();

        foreach ($attributeCollection as $attribute)
        {
            $attributeCode = $attribute->getAttributeCode ();
            $frontendLabel = Mage::helper ('erp')->__($attribute->getFrontendLabel ());

            $result [] = array (
                'value' => $attribute->getData ($this->_attributeField),
                'label' => sprintf ('%s ( %s )', $frontendLabel, $attributeCode),
            );
        }

        return $result;
    }

    public function toArray ()
    {
        $result = null;

        foreach ($this->toOptionArray () as $option)
        {
            $result [$option ['value']] = $option ['label'];
        }

        return $result;
    }
}

