<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Cron_Product extends EasySoftware_ERP_Model_Cron_Abstract
{
    const DEFAULT_QUEUE_LIMIT = 60;

    private function readERPProductsAPI ()
    {
        $companyId = $this->getStoreConfig ('company_id');
        $limit     = $this->getQueueConfig ('limit') ?? self::DEFAULT_QUEUE_LIMIT;

$query = <<< QUERY
    SELECT FIRST {$limit} * FROM PRODUTO
    WHERE EMPRESA = {$companyId}
    AND DATAHORA_ENVIADO IS NULL
    OR DATAHORA_ATUALIZADO > DATAHORA_ENVIADO
QUERY;

        $result = Mage::helper ('erp')->query ($query);

        while ($row = ibase_fetch_object ($result))
        {
            $product = Mage::getModel ('erp/product')->load ($row->CODIGO, 'external_id');

            $product->setExternalId ($row->CODIGO)
                ->setExternalSku ($row->REFERENCIA)
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

    private function readERPProductsCollection ()
    {
        $limit = $this->getQueueConfig ('product');

        $collection = Mage::getModel ('erp/product')->getCollection ()
            ->addFieldToFilter ('status', array ('neq' => EasySoftware_ERP_Helper_Data::STATUS_OKAY))
        ;

        $collection->getSelect ()
            ->where ('synced_at IS NULL OR synced_at < updated_at')
            ->limit ($limit ?? self::DEFAULT_QUEUE_LIMIT)
        ;

        return $collection;
    }

    private function updateERPProductsMagento ($collection)
    {
        foreach ($collection as $product)
        {
            $result = null;

            try
            {
                $result = $this->updateERPProduct ($product);
            }
            catch (Exception $e)
            {
                $this->logERPProduct ($product, $e->getMessage ());

                self::logException ($e);
            }

            if (!empty ($result)) $this->cleanupERPProduct ($product);
        }

        return true;
    }

    private function updateERPProduct ($product)
    {
        $externalId  = $product->getExternalId ();
        $externalSku = $product->getExternalSku ();
        $companyId   = $product->getCompanyId ();

$query = <<< QUERY
    SELECT * FROM PRODUTO
    WHERE CODIGO = {$externalId}
    AND EMPRESA = {$companyId}
QUERY;

        $result = Mage::helper ('erp')->query ($query);

        $row = ibase_fetch_object ($result);

        if (empty ($row) || !is_object ($row))
        {
            throw new Exception (Mage::helper ('erp')->__('Product not found: %s [ %s ]', $externalSku, $externalId));
        }

        $mageProduct = Mage::getModel ('catalog/product')->loadByAttribute (EasySoftware_ERP_Helper_Data::PRODUCT_ATTRIBUTE_ID, $product->getExternalId ());

        if (!$mageProduct || !$mageProduct->getId ())
        {
            $mageProduct = Mage::getModel ('catalog/product')
                ->setData (EasySoftware_ERP_Helper_Data::PRODUCT_ATTRIBUTE_ID, $product->getExternalId ())
                ->setAttributeSetId ($this->getUtils ()->getDefaultAttributeSetId ())
                ->setTypeId (Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                ->setVisibility (Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                ->setSku ($externalSku)
                ->setTaxClassId (0)
                ->setPrice (999999)
                ->setWeight (999999)
            ;
        }

        $mageProduct
            ->setName ($this->getHelper ()->ucfirst ($product->getName ()))
            ->setPrice (floatval ($row->PR_VENDA))
            ->setShortDescription ($this->getHelper ()->ucfirst (utf8_encode ($row->APLICACAO)))
            ->setStatus (
                $product->getIsActive ()
                ? Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                : Mage_Catalog_Model_Product_Status::STATUS_DISABLED
            )
        ;

        if (!empty ($row->PESO_B) && floatval ($row->PESO_B) > 0)
        {
            $mageProduct->setWeight ($row->PESO_B * 1000);
        }

        $mageProduct->save ();

        $stockItem = Mage::getModel ('cataloginventory/stock_item')
            ->assignProduct ($mageProduct)
            ->setStockId (Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID)
            ->setUseConfigManageStock (true)
            ->setManageStock (true)
            ->setIsInStock (true)
            ->setStockStatusChangedAuto (true)
            ->setQty (floatval ($row->QTD_ATUAL))
            ->save ()
        ;

        $mageProduct = Mage::getModel ('catalog/product')->load ($mageProduct->getId ());

        $product->setTypeId ($mageProduct->getTypeId ())
            ->setProductId ($mageProduct->getId ())
            ->setProductSku ($mageProduct->getSku ())
        ;

        return $mageProduct->getId ();
    }

    private function cleanupERPProduct ($product)
    {
        $now = Mage::getModel ('core/date')->date ('Y-m-d H:i:s');

        $externalId = $product->getExternalId ();
        $companyId = $product->getCompanyId ();

$query = <<< QUERY
    UPDATE PRODUTO SET DATAHORA_ENVIADO = '{$now}'
    WHERE CODIGO = {$externalId} AND EMPRESA = {$companyId}
QUERY;

        $result = Mage::helper ('erp')->query ($query);

        if ($result == 1)
        {
            $product->setSyncedAt ($now)
                ->setStatus (EasySoftware_ERP_Helper_Data::STATUS_OKAY)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->save ();
        }
    }

    private function logERPProduct ($product, $message = null)
    {
        $product->setStatus (EasySoftware_ERP_Helper_Data::STATUS_ERROR)
            ->setMessage ($message)
            ->save ();
    }

    public function run ()
    {
        if ($this->getStoreConfig ('active'))
        {
            $result = $this->readERPProductsAPI ();

            $collection = $this->readERPProductsCollection ();

            if ($collection->getSize ())
            {
                $this->updateERPProductsMagento ($collection);
            }
        }
    }
}

