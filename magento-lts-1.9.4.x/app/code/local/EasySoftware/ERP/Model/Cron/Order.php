<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Cron_Order extends EasySoftware_ERP_Model_Cron_Abstract
{
    const DEFAULT_QUEUE_LIMIT = 60;

    private function readERPOrdersMagento ()
    {
        $companyId = $this->getStoreConfig ('company_id');

        $limit = self::DEFAULT_QUEUE_LIMIT;

        $collection = Mage::getModel ('sales/order')->getCollection ()
            ->addFieldToFilter ('state', array ('eq' => Mage_Sales_Model_Order::STATE_NEW))
        ;

        $collection->getSelect ()
            ->joinLeft(
                array ('erp' => EasySoftware_ERP_Helper_Data::ORDER_TABLE),
                'main_table.entity_id = erp.order_id',
                array ()
            )
            ->where ('main_table.created_at > erp.synced_at OR erp.synced_at IS NULL')
        ;

        foreach ($collection as $mageOrder)
        {
            foreach ($mageOrder->getAllVisibleItems () as $item)
            {
                $detailId = $item->getProduct ()->getData (EasySoftware_ERP_Helper_Data::PRODUCT_ATTRIBUTE_ID);

                if (intval ($detailId) == 0)
                {
                    continue 2;
                }
            }

            $order = Mage::getModel ('erp/order')->load ($mageOrder->getId (), 'order_id');

            $customerTaxvat = preg_replace ('[\D]', '', $mageOrder->getCustomerTaxvat ());

            $order->setCustomerId ($mageOrder->getCustomerId ())
                ->setCustomerEmail ($mageOrder->getCustomerEmail ())
                ->setCustomerName ($mageOrder->getCustomerName ())
                ->setCustomerTaxvat ($customerTaxvat)
                ->setOrderId ($mageOrder->getId ())
                ->setOrderIncrementId ($mageOrder->getIncrementId ())
                ->setOrderBaseGrandTotal ($mageOrder->getBaseGrandTotal ())
                ->setCompanyId ($companyId)
                ->setStatus (EasySoftware_ERP_Helper_Data::STATUS_PENDING)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }
    }

    private function readERPOrdersCollection ()
    {
        $limit = self::DEFAULT_QUEUE_LIMIT;

        $collection = Mage::getModel ('erp/order')->getCollection ()
            ->addFieldToFilter ('status', array ('neq' => EasySoftware_ERP_Helper_Data::STATUS_OKAY))
        ;

        $collection->getSelect ()
            ->where ('synced_at IS NULL OR synced_at < updated_at')
            ->limit ($limit ?? self::DEFAULT_QUEUE_LIMIT)
        ;

        return $collection;
    }

    private function updateERPOrdersAPI ($collection)
    {
        foreach ($collection as $order)
        {
            $result = null;

            try
            {
                $result = $this->updateERPOrder ($order);
            }
            catch (Exception $e)
            {
                $this->logERPOrder ($order, $e->getMessage ());

                self::logException ($e);
            }

            if (!empty ($result)) $this->cleanupERPOrder ($order);
        }

        return true;
    }

    private function updateERPOrder ($order)
    {
        $companyId  = $order->getCompanyId ();

        $mageOrder = Mage::getModel ('sales/order')->load ($order->getOrderId ());

        if (!$mageOrder || !$mageOrder->getId ())
        {
            throw new Exception (Mage::helper ('erp')->__('Order was not found.'));
        }

        foreach ($mageOrder->getAllVisibleItems () as $item)
        {
            $detailId = $item->getProduct ()->getData (EasySoftware_ERP_Helper_Data::PRODUCT_ATTRIBUTE_ID);

            if (intval ($detailId) == 0)
            {
                throw new Exception (Mage::helper ('erp')->__('Product was not found: %s %s %s', $item->getName (), $item->getSku (), $detailId));
            }
        }

        $collection = Mage::getModel ('erp/customer')->getCollection ()
            ->addFieldToFilter ('customer_id', array ('eq' => $mageOrder->getCustomerId ()))
            ->addFieldToFilter ('company_id', array ('eq' => $companyId))
            ->addFieldToFilter ('is_active', array ('eq' => '1'))
            ->addFieldToFilter ('status', array ('eq' => EasySoftware_ERP_Helper_Data::STATUS_OKAY))
        ;

        $customerId = $collection->getSize ()
            ? $collection->getFirstItem ()->getExternalId ()
            : $this->getOrderConfig ('customer_guest_id')
        ;

        $createdAt = Mage::getModel ('core/date')
            ->date ('Y-m-d', strtotime ($mageOrder->getCreatedAt ()))
        ;

        $subTotal       = $mageOrder->getBaseSubtotal ();
        $discountAmount = $mageOrder->getBaseDiscountAmount ();
        $grandTotal     = $mageOrder->getBaseGrandTotal ();

        $cashAmount     = 0;
        $changeAmount   = 0;

        $customerTaxvat   = $order->getCustomerTaxvat ();
        $customerName     = $order->getCustomerName ();
        $orderIncrementId = $order->getOrderIncrementId ();

        $defaultUserId    = $this->getOrderConfig ('default_user_id');
        $defaultCashierId = $this->getOrderConfig ('default_cashier_id');
        $defaultSellerId  = $this->getOrderConfig ('default_seller_id');
        $defaultBatchId   = $this->getOrderConfig ('default_batch_id');

$queryMax = <<< QUERY
    SELECT MAX(CODIGO) FROM VENDAS_MASTER
    WHERE FKEMPRESA = {$companyId}
QUERY;

        $result = Mage::helper ('erp')->query ($queryMax);

        $row = ibase_fetch_object ($result);

        $saleId = is_object ($row) && intval ($row->MAX) > 0 ? $row->MAX + 1: 1;

$queryOrder = <<< QUERY
    INSERT INTO VENDAS_MASTER(
        CODIGO,
        DATA_EMISSAO,
        DATA_SAIDA,
        ID_CLIENTE,
        FK_USUARIO,
        FK_CAIXA,
        FK_VENDEDOR,
        CPF_NOTA,
        SUBTOTAL,
        TIPO_DESCONTO,
        DESCONTO,
        TROCO,
        DINHEIRO,
        TOTAL,
        SITUACAO,
        FKEMPRESA,
        PERCENTUAL,
        TIPO,
        LOTE,
        PERCENTUAL_ACRESCIMO,
        ACRESCIMO,
        FK_TABELA,
        PEDIDO,
        NOME,
        TELA
    )
    VALUES(
        $saleId,
        '{$createdAt}',
        '{$createdAt}',
        {$customerId},
        {$defaultUserId},
        {$defaultCashierId},
        {$defaultSellerId},
        '{$customerTaxvat}',
        {$subTotal},
        'D',
        $discountAmount,
        $changeAmount,
        $cashAmount,
        $grandTotal,
        'F',
        {$companyId},
        0,
        'P',
        {$defaultBatchId},
        0,
        0,
        1,
        '{$orderIncrementId}',
        '{$customerName}',
        'PDV'
    );
QUERY;

        $result = Mage::helper ('erp')->query ($queryOrder);

        if ($result != 1)
        {
            throw new Exception (Mage::helper ('erp')->__('Unable to save order.'));
        }

        $order->setExternalId ($saleId)
            ->setExternalCode ($saleId)
            ->save ()
        ;

        $line = 1;

        foreach ($mageOrder->getAllVisibleItems () as $item)
        {

$queryMax = <<< QUERY
    SELECT MAX(CODIGO) FROM VENDAS_DETALHE
QUERY;

            $result = Mage::helper ('erp')->query ($queryMax);

            $row = ibase_fetch_object ($result);

            $detailId = is_object ($row) && intval ($row->MAX) > 0 ? $row->MAX + 1: 1;

            $qtyOrdered = $item->getQtyOrdered ();
            $basePrice  = $item->getBasePrice ();
            $baseDiscountAmount = $item->getBaseDiscountAmount ();
            $baseRowTotal       = $item->getBaseRowTotal ();

            $productPrice    = $item->getProduct ()->getPrice ();
            $productDetailId = $item->getProduct ()->getData (EasySoftware_ERP_Helper_Data::PRODUCT_ATTRIBUTE_ID);

$queryDetail = <<< QUERY
    INSERT INTO VENDAS_DETALHE(
        CODIGO,
        FKVENDA,
        ID_PRODUTO,
        ITEM,
        QTD,
        E_MEDIO,
        PRECO,
        VALOR_ITEM,
        VDESCONTO,
        TOTAL,
        SITUACAO,
        UNIDADE,
        QTD_DEVOLVIDA,
        ACRESCIMO,
        FK_GRADE,
        ID_SERIAL
    )
    VALUES(
        {$detailId},
        {$saleId},
        {$productDetailId},
        {$line},
        {$qtyOrdered},
        0,
        {$productPrice},
        {$baseRowTotal},
        {$baseDiscountAmount},
        {$baseRowTotal},
        'F',
        'UN',
        0,
        0,
        0,
        0
    );
QUERY;

            $result = Mage::helper ('erp')->query ($queryDetail);

            if ($result != 1)
            {
                throw new Exception (Mage::helper ('erp')->__('Unable to save order item: %s %s %s', $item->getName (), $item->getSku (), $productDetailId));
            }

            $line ++;
        }

        return $mageOrder->getId ();
    }

    private function cleanupERPOrder ($order)
    {
        $order->setSyncedAt (date ('c'))
            ->setStatus (EasySoftware_ERP_Helper_Data::STATUS_OKAY)
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->save ();
    }

    private function logERPOrder ($order, $message = null)
    {
        $order->setStatus (EasySoftware_ERP_Helper_Data::STATUS_ERROR)
            ->setMessage ($message)
            ->save ();
    }

    public function run ()
    {
        if ($this->getStoreConfig ('active'))
        {
            $result = $this->readERPOrdersMagento ();

            $collection = $this->readERPOrdersCollection ();

            if ($collection->getSize ())
            {
                $this->updateERPOrdersAPI ($collection);
            }
        }
    }
}

