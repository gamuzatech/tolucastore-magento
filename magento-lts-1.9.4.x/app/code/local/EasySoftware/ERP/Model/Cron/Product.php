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
        $limit     = $this->getQueueConfig ('product') ?? self::DEFAULT_QUEUE_LIMIT;

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

        $row = ibase_fetch_object ($result, IBASE_TEXT);

        if (empty ($row) || !is_object ($row))
        {
            throw new Exception (Mage::helper ('erp')->__('Product not found: %s [ %s ]', $externalSku, $externalId));
        }

        $mageProduct = Mage::getModel ('catalog/product')->loadByAttribute (EasySoftware_ERP_Helper_Data::PRODUCT_ATTRIBUTE_ID, $product->getExternalId ());

        if (!$mageProduct || !$mageProduct->getId ())
        {
            $mageProduct = Mage::getModel ('catalog/product')
                ->setData (EasySoftware_ERP_Helper_Data::PRODUCT_ATTRIBUTE_ID, $product->getExternalId ())
                ->setWebsiteIds (array (Mage::app ()->getStore (Mage_Core_Model_App::DISTRO_STORE_ID)->getWebsite ()->getId ()))
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

        $groupId = $row->GRUPO;

        if (!empty ($groupId) && intval ($groupId) > 0)
        {
            $mageCategory = Mage::getModel ('catalog/category')->loadByAttribute (EasySoftware_ERP_Helper_Data::CATEGORY_ATTRIBUTE_ID, $groupId);

            if ($mageCategory && $mageCategory->getId ())
            {
                $mageProduct->setCategoryIds (array ($mageCategory->getId ()));
            }
        }

        $brandId = $row->FK_MARCA;

        if (!empty ($brandId) && intval ($brandId) > 0)
        {
            $brand = Mage::getModel ('erp/brand')->load ($brandId, 'external_id');

            if ($brand && $brand->getId () && $brand->getOptionId ())
            {
                $mageProduct->setBrand ($brand->getOptionId ());
            }
        }

        $mageProduct->setSpecialPrice ($row->PRECO_PROMO_VAREJO)
            ->setSpecialFromDate ($row->INICIO_PROMOCAO)
            ->setSpecialToDate ($row->FIM_PROMOCAO)
        ;

        $mageProduct->save ();

        $pendingQty = $this->_getPendingQty ($mageProduct->getSku ());

        $stockItem = Mage::getModel ('cataloginventory/stock_item')
            ->assignProduct ($mageProduct)
            ->setStockId (Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID)
            ->setUseConfigManageStock (true)
            ->setManageStock (true)
            ->setIsInStock (true)
            ->setStockStatusChangedAuto (true)
            ->setQty (floatval ($row->QTD_ATUAL) - $pendingQty)
            ->save ()
        ;

        $mediaApi = Mage::getModel ('catalog/product_attribute_media_api');
        $mediaDir = Mage::getBaseDir ('media');

        foreach ($mediaApi->items ($mageProduct->getId ()) as $item)
        {
            $mediaApi->remove ($mageProduct->getId (), $item ['file']);

            @ unlink ($mediaDir . DS . 'catalog' . DS . 'product' . $item ['file']);
        }

        if (!empty ($row->FOTO))
        {
            $temp = tempnam (sys_get_temp_dir (), 'ERP');

            file_put_contents ($temp, $row->FOTO);

            $im = imagecreatefrombmp ($temp);
            imagepng ($im, $temp);
            imagedestroy($im);

            $photo = file_get_contents ($temp);

            $image = array ();
            $image ['file'] = array ('content' => base64_encode ($photo), 'mime' => 'image/png');
            $image ['types'] = array ('image', 'small_image', 'thumbnail');
            $image ['exclude'] = 0;

            try
            {
                $mediaApi->create ($mageProduct->getId (), $image);
            }
            catch (Exception $e)
            {
                throw new Exception (Mage::helper ('erp')->__('Unable to save product image.'));
            }
        }

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
        $productSku = $product->getProductSku ();

$query = <<< QUERY
    UPDATE PRODUTO SET REFERENCIA = '{$productSku}', DATAHORA_ENVIADO = '{$now}'
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

    public function _getPendingQty ($sku)
    {
        $collection = Mage::getModel ('sales/order_item')->getCollection ()
            ->addFieldToFilter ('main_table.sku', array ('eq' => $sku))
        ;

        $collection->getSelect ()
            ->join(
                array ('order' => Mage::getSingleton ('core/resource')->getTableName ('sales/order')),
                'main_table.order_id = order.entity_id',
                array ('state')
            )
            ->where ('order.state = ?', Mage_Sales_Model_Order::STATE_NEW)
            ->group ('main_table.sku')
            ->reset (Zend_Db_Select::COLUMNS)
            ->columns (array(
                'pending_qty' => 'SUM(qty_ordered)'
            ))
        ;

        return $collection->getFirstItem ()->getPendingQty ();
    }
}

