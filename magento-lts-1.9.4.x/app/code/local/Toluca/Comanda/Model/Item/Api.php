<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Mesa API
 */
class Toluca_Comanda_Model_Item_Api extends Mage_Api_Model_Resource_Abstract
{
    public function items ($mesaId = null)
    {
        $result = array ();

        $collection = Mage::getModel ('comanda/item')->getCollection ()
            ->addFieldToFilter ('mesa_id',  array ('eq' => $mesaId))
            ->addFieldToFilter ('order_id', array ('null' => true))
        ;

        $collection->getSelect ()
            ->join(
                array ('mesa' => Mage::getSingleton ('core/resource')->getTablename ('comanda/mesa')),
                'main_table.mesa_id = mesa.entity_id',
                array (
                    'mesa_name' => 'mesa.name',
                    'mesa_description' => 'mesa.description',
                )
            )
        ;

        foreach ($collection as $item)
        {
            $result = array(
                'entity_id'   => intval ($item->getId ()),
                'mesa_id'     => intval ($item->getMesaId ()),
                'mesa_name'   => $item->getMesaName (),
                'mesa_description' => $item->getMesaDescription (),
                'order_id'    => intval ($item->getOrderId ()),
                'order_increment_id' => $item->getOrderIncrementId (),
                'product_id'  => intval ($item->getProductId ()),
                'sku'         => $item->getSku (),
                'name'        => $item->getName (),
                'price'       => floatval ($item->getPrice ()),
                'qty'         => intval ($item->getQty ()),
                'total'       => floatval ($item->getTotal ()),
                'options'            => $item->getOptions (),
                'additional_options' => $item->getAdditionalOptions (),
                'super_attribute'    => $item->getSuperAttribute (),
                'bundle_option'      => $item->getBundleOption (),
                'created_at'  => $item->getCreatedAt (),
                'updated_at'  => $item->getUpdatedAt (),
            );
        }

        return $result;
    }

    public function add ($mesaId = null, $itemsData = null)
    {
        if (empty ($mesaId))
        {
            $this->_fault ('mesa_not_specified');
        }

        $mesa = Mage::getModel ('comanda/mesa')->load ($mesaId);

        if (!$mesa || !$mesa->getId ())
        {
            $this->_fault ('mesa_not_exists');
        }

        if (!$mesa->getIsActive ())
        {
            $this->_fault ('mesa_is_not_enabled');
        }

        if (!is_array ($itemsData) || !count ($itemsData))
        {
            $this->_fault ('item_data_not_specified');
        }

        foreach ($itemsData as $item)
        {
            unset ($item ['entity_id']);

            $sku = $item ['sku'];
            $qty = $item ['qty'];

            $mageProduct = Mage::getModel ('catalog/product')->loadByAttribute ('sku', $sku);

            if (!$mageProduct || !$mageProduct->getId ())
            {
                $this->_fault ('product_not_exists', Mage::helper ('comanda')->__('Product not exists: %s', $sku));
            }

            $stockItem = Mage::getModel ('cataloginventory/stock_item')->assignProduct ($mageProduct);

            if (!$stockItem->getIsInStock () || $stockItem->getQty () < $qty)
            {
                $this->_fault ('invalid_product_qty', Mage::helper ('comanda')->__('Invalid product qty: %s %s', $qty, $sku));
            }

            $item = Mage::getModel ('comanda/item')
                ->addData ($item)
                ->setMesaId ($mesaId)
                ->setProductId ($mageProduct->getId ())
                ->setName ($mageProduct->getName ())
                ->setPrice ($mageProduct->getFinalPrice ())
                ->setTotal ($mageProduct->getFinalPrice () * $qty)
                ->setCreatedAt (date ('c'))
                ->save ()
            ;
        }

        $collection = Mage::getModel ('comanda/item')->getCollection ()
            ->addFieldToFilter ('mesa_id',  array ('eq' => $mesaId))
            ->addFieldToFilter ('order_id', array ('null' => true))
        ;

        if ($collection->getSize ())
        {
            $mesa->setStatus (Toluca_Comanda_Helper_Data::MESA_STATUS_BUSY)
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }

        return true;
    }

    public function remove ($mesaId = null, $itemsData = null)
    {
        if (empty ($mesaId))
        {
            $this->_fault ('mesa_not_specified');
        }

        $mesa = Mage::getModel ('comanda/mesa')->load ($mesaId);

        if (!$mesa || !$mesa->getId ())
        {
            $this->_fault ('mesa_not_exists');
        }

        if (!$mesa->getIsActive ())
        {
            $this->_fault ('mesa_is_not_enabled');
        }

        if (!is_array ($itemsData) || !count ($itemsData))
        {
            $this->_fault ('item_data_not_specified');
        }

        foreach ($itemsData as $item)
        {
            $collection = Mage::getModel ('comanda/item')->getCollection ()
                ->addFieldToFilter ('mesa_id',  array ('eq' => $mesaId))
                ->addFieldToFilter ('order_id', array ('null' => true))
                ->addFieldToFilter ('sku',      array ('eq' => $item ['sku']))
            ;

            if (!empty ($item ['entity_id']))
            {
                $collection->addFieldToFilter ('entity_id', array ('eq' => $item ['entity_id']));
            }

            foreach ($collection as $_item)
            {
                $_item->delete ();
            }
        }

        $collection = Mage::getModel ('comanda/item')->getCollection ()
            ->addFieldToFilter ('mesa_id',  array ('eq' => $mesaId))
            ->addFieldToFilter ('order_id', array ('null' => true))
        ;

        if (!$collection->getSize ())
        {
            $mesa->setStatus (Toluca_Comanda_Helper_Data::MESA_STATUS_FREE)
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }

        return true;
    }
}

