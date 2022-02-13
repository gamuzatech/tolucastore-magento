<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Utils
{
    public function addAttributeOptionValue ($attributeId, $data)
    {
        $label   = $data ['label'];
        $order   = $data ['order'];
        $default = $data ['default'];

        $resource = Mage::getSingleton ('core/resource');
        $write = $resource->getConnection ('core_write');

        $tableAttribute            = $resource->getTableName ('eav_attribute');
        $tableAttributeOption      = $resource->getTableName ('eav_attribute_option');
        $tableAttributeOptionValue = $resource->getTableName ('eav_attribute_option_value');

        $optionId = -1;

        foreach ($label as $value)
        {
            $storeCode  = $value ['store_code'];
            $storeValue = $value ['value'];

            $storeId = Mage::app ()->getStore ($storeCode)->getId ();

            if ($storeId == 0)
            {
                $optionId = $this->getAttributeOptionIdByValue ($attributeId, $storeValue, $storeId);

                if ($optionId < 0)
                {
                    $write->insert ($tableAttributeOption, array ('attribute_id' => $attributeId, 'sort_order' => $order));

                    $optionId = $write->lastInsertId ();
                }
                else
                {
                    $write->insertOnDuplicate ($tableAttributeOption, array ('option_id' => $optionId, 'attribute_id' => $attributeId, 'sort_order' => $order));
                }
            }

            $value_id = $this->getAttributeOptionValueId ($optionId, $storeId);

            $tValue = trim ($storeValue);

            $write->insertOnDuplicate ($tableAttributeOptionValue, array ('value_id' => $value_id, 'option_id' => $optionId, 'store_id' => $storeId, 'value' => $tValue));
            
            if ($default)
            {
                $write->update ($tableAttribute, array('default_value' => $optionId), "attribute_id = {$attributeId}");
            }
        }

        return $optionId;
    }

    public function getAttributeOptionIdByValue ($attributeId, $value, $storeId = 0)
    {
        $resource = Mage::getSingleton ('core/resource');
        $read = $resource->getConnection ('core_read');

        $tableAttributeOption      = $resource->getTableName ('eav_attribute_option');
        $tableAttributeOptionValue = $resource->getTableName ('eav_attribute_option_value');

        $tValue = trim ($value);

        $select = $read->select ()
            ->from (array ('eaov' => $tableAttributeOptionValue), array ('option_id' => 'eaov.option_id'))
            ->join (array ('eao' => $tableAttributeOption), 'eaov.option_id = eao.option_id', null, null)
            ->where ("eao.attribute_id = {$attributeId} AND eaov.store_id = {$storeId} AND eaov.value = '{$tValue}'");

        $children = $read->fetchAll ($select);

        $optionId = count ($children) ? $children [0]['option_id'] : -1;

        return (int) $optionId;
    }

    public function getAttributeOptionValueId ($optionId, $storeId = 0)
    {
        $resource = Mage::getSingleton ('core/resource');
        $read = $resource->getConnection ('core_read');

        $tableAttributeOptionValue = $resource->getTableName ('eav_attribute_option_value');

        $select = $read->select ()
            ->from (array ('eaov' => $tableAttributeOptionValue), array ('value_id' => 'eaov.value_id'))
            ->where ("eaov.option_id = {$optionId} AND eaov.store_id = {$storeId}");

        $children = $read->fetchAll ($select);

        $valueId = count ($children) ? $children [0]['value_id'] : -1;

        return (int) $valueId;
    }
}

