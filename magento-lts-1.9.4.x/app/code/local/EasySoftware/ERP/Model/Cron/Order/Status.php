<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Cron_Order_Status extends EasySoftware_ERP_Model_Cron_Abstract
{
    const DEFAULT_QUEUE_LIMIT = 60;

    public function run ()
    {
        if (!$this->getStoreConfig ('active'))
        {
            return false;
        }

        $limit = self::DEFAULT_QUEUE_LIMIT;

        $collection = Mage::getModel ('erp/order')->getCollection ()
            ->addFieldToFilter ('main_table.status',      array ('eq' => EasySoftware_ERP_Helper_Data::STATUS_OKAY))
            ->addFieldToFilter ('main_table.is_canceled', array ('eq' => '0'))
        ;

        $collection->getSelect ()
            ->joinLeft(
                array ('sfo' => Mage::getSingleton ('core/resource')->getTablename ('sales/order')),
                'main_table.order_id = sfo.entity_id',
                array ('state')
            )
            ->where ('sfo.state = ?', Mage_Sales_Model_Order::STATE_CANCELED)
            ->limit ($limit ?? self::DEFAULT_QUEUE_LIMIT)
        ;

        foreach ($collection as $order)
        {
            $companyId  = $order->getCompanyId ();
            $externalId = $order->getExternalId ();

$queryUpdate = <<< QUERY
    UPDATE VENDAS_MASTER SET SITUACAO = 'C'
    WHERE FKEMPRESA = {$companyId} AND CODIGO = {$externalId}
QUERY;

            $result = Mage::helper ('erp')->query ($queryUpdate);

            if ($result != 1)
            {
                $this->message (Mage::helper ('erp')->__('Unable to cancel order: %s', $order->getOrderIncrementId ()));
            }
            else
            {
                $order->setIsCanceled (1)->save ();
            }
        }
    }
}

