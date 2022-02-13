<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Cron_Group extends EasySoftware_ERP_Model_Cron_Abstract
{
    const DEFAULT_QUEUE_LIMIT = 60;

    private function readERPGroupsAPI ()
    {
        $companyId = $this->getStoreConfig ('company_id');
        $limit     = $this->getQueueConfig ('limit') ?? self::DEFAULT_QUEUE_LIMIT;

$query = <<< QUERY
    SELECT FIRST {$limit} * FROM GRUPO
    WHERE EMPRESA = {$companyId}
    AND DATAHORA_ENVIADO IS NULL
    OR DATAHORA_ATUALIZADO > DATAHORA_ENVIADO
QUERY;

        $result = Mage::helper ('erp')->query ($query);

        while ($row = ibase_fetch_object ($result))
        {
            $group = Mage::getModel ('erp/group')->load ($row->CODIGO, 'external_id');

            $group->setExternalId ($row->CODIGO)
                ->setCompanyId ($row->EMPRESA)
                ->setName (utf8_encode ($row->DESCRICAO))
                ->setIsActive (!strcmp ($row->ATIVO, 'S'))
                ->setStatus (EasySoftware_ERP_Helper_Data::STATUS_PENDING)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }
    }

    private function readERPGroupsCollection ()
    {
        $limit = $this->getQueueConfig ('group');

        $collection = Mage::getModel ('erp/group')->getCollection ()
            ->addFieldToFilter ('status', array ('neq' => EasySoftware_ERP_Helper_Data::STATUS_OKAY))
        ;

        $collection->getSelect ()
            ->where ('synced_at IS NULL OR synced_at < updated_at')
            ->limit ($limit ?? self::DEFAULT_QUEUE_LIMIT)
        ;

        return $collection;
    }

    private function updateERPGroupsMagento ($collection)
    {
        foreach ($collection as $group)
        {
            $result = null;

            try
            {
                $result = $this->updateERPGroup ($group);
            }
            catch (Exception $e)
            {
                $this->logERPGroup ($group, $e->getMessage ());

                self::logException ($e);
            }

            if (!empty ($result)) $this->cleanupERPGroup ($group);
        }

        return true;
    }

    private function updateERPGroup ($group)
    {
        $mageCategory = Mage::getModel ('catalog/category')->loadByAttribute (EasySoftware_ERP_Helper_Data::CATEGORY_ATTRIBUTE_ID, $group->getExternalId ());

        if (!$mageCategory || !$mageCategory->getId ())
        {
            $parentCategoryId = Mage::app ()
                ->getStore (Mage_Core_Model_App::DISTRO_STORE_ID)
                ->getGroup ()
                ->getRootCategoryId ()
            ;

            $parentCategory = Mage::getModel ('catalog/category')->load ($parentCategoryId);

            $mageCategory = Mage::getModel ('catalog/category')
                ->setData (EasySoftware_ERP_Helper_Data::CATEGORY_ATTRIBUTE_ID, $group->getExternalId ())
                ->setPath ($parentCategory->getPath ())
                ->setDisplayModel (Mage_Catalog_Model_Category::DM_PRODUCT)
                ->setIsAnchor (1)
            ;
        }

        $mageCategory
            ->setIsActive ($group->getIsActive ())
            ->setName ($this->getHelper ()->ucfirst ($group->getName ()))
            ->save ();

        $group->setCategoryId ($mageCategory->getId ());

        return $mageCategory->getId ();
    }

    private function cleanupERPGroup ($group)
    {
        $now = Mage::getModel ('core/date')->date ('Y-m-d H:i:s');

        $externalId = $group->getExternalId ();
        $companyId = $group->getCompanyId ();

$query = <<< QUERY
    UPDATE GRUPO SET DATAHORA_ENVIADO = '{$now}'
    WHERE CODIGO = {$externalId} AND EMPRESA = {$companyId}
QUERY;

        $result = Mage::helper ('erp')->query ($query);

        if ($result == 1)
        {
            $group->setSyncedAt ($now)
                ->setStatus (EasySoftware_ERP_Helper_Data::STATUS_OKAY)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->save ();
        }
    }

    private function logERPGroup ($group, $message = null)
    {
        $group->setStatus (EasySoftware_ERP_Helper_Data::STATUS_ERROR)
            ->setMessage ($message)
            ->save ();
    }

    public function run ()
    {
        if ($this->getStoreConfig ('active'))
        {
            $result = $this->readERPGroupsAPI ();

            $collection = $this->readERPGroupsCollection ();

            if ($collection->getSize ())
            {
                $this->updateERPGroupsMagento ($collection);
            }
        }
    }
}

