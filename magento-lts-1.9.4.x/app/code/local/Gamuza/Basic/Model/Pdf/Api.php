<?php
/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2023 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * PDF API
 */
class Gamuza_Basic_Model_Pdf_Api extends Mage_Api_Model_Resource_Abstract
{
    public function service ($order_id)
    {
        if (empty ($order_id))
        {
            $this->_fault ('order_not_specified');
        }

        $order = Mage::getModel ('sales/order')
            ->setStoreId (Mage_Core_Model_App::DISTRO_STORE_ID)
            ->load ($order_id)
        ;

        if (!$order || !$order->getId ())
        {
            $this->_fault ('order_not_exists');
        }

        $service = Mage::getModel ('basic/order_service')->load ($order->getId (), 'order_id');

        if (!$service || !$service->getId ())
        {
            $this->_fault ('service_not_exists');
        }

        $emulation = Mage::getModel ('core/app_emulation');

        $oldEnvironment = $emulation->startEnvironmentEmulation(
            Mage_Core_Model_App::ADMIN_STORE_ID,
            Mage_Core_Model_App_Area::AREA_ADMINHTML,
            true
        );

        $pdf = Mage::getModel ('basic/sales_order_pdf_service')->getPdf (array ($service));

        $emulation->stopEnvironmentEmulation($oldEnvironment);

        return base64_encode ($pdf->render ());
    }
}

