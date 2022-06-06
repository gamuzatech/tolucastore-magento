<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Cron_Brand extends EasySoftware_ERP_Model_Cron_Abstract
{
    const DEFAULT_QUEUE_LIMIT = 60;

    private function readERPBrandsAPI ()
    {
        $companyId = $this->getStoreConfig ('company_id');
        $limit     = $this->getQueueConfig ('brand') ?? self::DEFAULT_QUEUE_LIMIT;

$query = <<< QUERY
    SELECT FIRST {$limit} * FROM MARCA
    WHERE EMPRESA = {$companyId}
    AND DATAHORA_ENVIADO IS NULL
    OR DATEDIFF(SECOND FROM DATAHORA_ATUALIZADO TO DATAHORA_ENVIADO) <> 0
QUERY;

        $result = Mage::helper ('erp')->query ($query);

        while ($row = ibase_fetch_object ($result))
        {
            $brand = Mage::getModel ('erp/brand')->load ($row->CODIGO, 'external_id');

            $brand->setExternalId ($row->CODIGO)
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

    private function readERPBrandsCollection ()
    {
        $limit = $this->getQueueConfig ('brand');

        $collection = Mage::getModel ('erp/brand')->getCollection ()
            ->addFieldToFilter ('status', array ('neq' => EasySoftware_ERP_Helper_Data::STATUS_OKAY))
        ;

        $collection->getSelect ()
            ->where ('synced_at IS NULL OR synced_at < updated_at')
            ->limit ($limit ?? self::DEFAULT_QUEUE_LIMIT)
        ;

        return $collection;
    }

    private function updateERPBrandsMagento ($collection)
    {
        foreach ($collection as $brand)
        {
            $result = null;

            try
            {
                $result = $this->updateERPBrand ($brand);
            }
            catch (Exception $e)
            {
                $this->logERPBrand ($brand, $e->getMessage ());

                self::logException ($e);
            }

            if (!empty ($result)) $this->cleanupERPBrand ($brand);
        }

        return true;
    }

    private function updateERPBrand ($brand)
    {
        $attributeCode = $this->getProductConfig ('brand');

        $attribute = Mage::getModel ('eav/entity_attribute')->loadByCode ('catalog_product', $attributeCode);

        if (!$attribute || !$attribute->getId ())
        {
            throw new Exception (Mage::helper ('erp')->__('Attribute not found: %s', $attributeCode));
        }

        $data = array(
            'default'    => null,
            'order'      => 0,
            'label'      => array(
                array ('store_code' => Mage_Core_Model_App::ADMIN_STORE_ID, 'value' => $this->getHelper ()->ucfirst ($brand->getName ())),
            ),
        );

        $optionId = $this->getUtils ()->addAttributeOptionValue ($attribute->getId (), $data);

        $brand->setAttributeId ($attribute->getId ())
            ->setOptionId ($optionId)
        ;

        return $optionId;
    }

    private function cleanupERPBrand ($brand)
    {
        $now = Mage::getModel ('core/date')->date ('Y-m-d H:i:s');

        $externalId = $brand->getExternalId ();
        $companyId = $brand->getCompanyId ();

$query = <<< QUERY
    UPDATE MARCA SET DATAHORA_ENVIADO = '{$now}'
    WHERE CODIGO = {$externalId} AND EMPRESA = {$companyId}
QUERY;

        $result = Mage::helper ('erp')->query ($query);

        if ($result == 1)
        {
            $brand->setSyncedAt (date ('c'))
                ->setStatus (EasySoftware_ERP_Helper_Data::STATUS_OKAY)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->save ();
        }
    }

    private function logERPBrand ($brand, $message = null)
    {
        $brand->setStatus (EasySoftware_ERP_Helper_Data::STATUS_ERROR)
            ->setMessage ($message)
            ->save ();
    }

    public function run ()
    {
        if ($this->getStoreConfig ('active'))
        {
            $result = $this->readERPBrandsAPI ();

            $collection = $this->readERPBrandsCollection ();

            if ($collection->getSize ())
            {
                $this->updateERPBrandsMagento ($collection);
            }
        }
    }
}

