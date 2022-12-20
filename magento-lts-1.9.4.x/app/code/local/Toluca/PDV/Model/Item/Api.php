<?php
/**
 * @package     Toluca_PDV
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Cashier API
 */
class Toluca_PDV_Model_Item_Api extends Mage_Api_Model_Resource_Abstract
{
    public function items ()
    {
        $result = array ();

        $collection = Mage::getModel ('pdv/item')->getCollection ();

        $collection->getSelect ()
            ->joinLeft (
                array ('user' => Mage::getSingleton ('core/resource')->getTableName ('pdv/user')),
                'main_table.user_id = user.entity_id',
                array (
                    'user_name' => 'user.name'
                )
            )
            ->joinLeft (
                array ('order' => Mage::getSingleton ('core/resource')->getTableName ('sales/order')),
                'main_table.entity_id = order.pdv_id AND order.is_pdv = 1',
                array (
                    'order_amount' => 'SUM(order.base_grand_total)'
                )
            )
        ;

        foreach ($collection as $item)
        {
            $result [] = array(
                'entity_id'  => intval ($item->getId ()),
                'name'       => $item->getName (),
                'is_active'  => boolval ($item->getIsActive ()),
                'status'     => intval ($item->getStatus ()),
                'user_id'    => intval ($item->getUserId ()),
                'user_name'  => $item->getUserName (),
                'created_at' => $item->getCreatedAt (),
                'updated_at' => $item->getUpdatedAt (),
                'opened_at' => $item->getOpenedAt (),
                'closed_at' => $item->getClosedAt (),
                'open_amount'      => floatval ($item->getOpenAmount ()),
                'reinforce_amount' => floatval ($item->getReinforceAmount ()),
                'bleed_amount'     => floatval ($item->getBleedAmount ()),
                'money_amount'     => floatval ($item->getMoneyAmount ()),
                'change_amount'    => floatval ($item->getChangeAmount ()),
                'close_amount'     => floatval ($item->getCloseAmount ()),
                'order_amount'     => floatval ($item->getOrderAmount ()),
            );
        }

        return $result;
    }

    public function info ($item_id)
    {
        if (empty ($item_id))
        {
            $this->_fault ('item_not_specified');
        }

        $item = Mage::getModel ('pdv/item')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$item || !$item->getId ())
        {
            $this->_fault ('item_not_exists');
        }

        $result = array(
            'entity_id'  => intval ($item->getId ()),
            'name'       => $item->getName (),
            'is_active'  => boolval ($item->getIsActive ()),
            'status'     => intval ($item->getStatus ()),
            'user_id'    => intval ($item->getUserId ()),
            'user_name'  => $item->getUserName (),
            'created_at' => $item->getCreatedAt (),
            'updated_at' => $item->getUpdatedAt (),
            'opened_at' => $item->getOpenedAt (),
            'closed_at' => $item->getClosedAt (),
            'open_amount'      => floatval ($item->getOpenAmount ()),
            'reinforce_amount' => floatval ($item->getReinforceAmount ()),
            'bleed_amount'     => floatval ($item->getBleedAmount ()),
            'money_amount'     => floatval ($item->getMoneyAmount ()),
            'change_amount'    => floatval ($item->getChangeAmount ()),
            'close_amount'     => floatval ($item->getCloseAmount ()),
            'order_amount'     => floatval ($item->getOrderAmount ()),
        );

        $user = Mage::getModel ('pdv/user')->load ($item->getUserId ());

        if ($user && $user->getId ())
        {
            $result ['user_name'] = $user->getName ();
        }

        $collection = Mage::getModel ('sales/order')->getCollection ()
            ->addFieldToFilter ('is_pdv', array ('eq' => true))
            ->addFieldToFilter ('pdv_id', array ('eq' => $item->getId ()))
        ;

        $collection->getSelect ()
            ->columns (array(
                'sum_base_grand_total' => 'SUM(base_grand_total)'
            ))
        ;

        if ($collection->getSize () > 0)
        {
            $result ['order_amount'] = floatval ($collection->getFirstItem ()->getSumBaseGrandTotal ());
        }

        return $result;
    }

    public function open ($item_id, $amount, $user_id, $password)
    {
        if (empty ($item_id))
        {
            $this->_fault ('item_not_specified');
        }

        if (empty ($amount))
        {
            $this->_fault ('amount_not_specified');
        }

        if (empty ($user_id))
        {
            $this->_fault ('user_not_specified');
        }

        if (empty ($password))
        {
            $this->_fault ('password_not_specified');
        }

        $item = Mage::getModel ('pdv/item')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$item || !$item->getId ())
        {
            $this->_fault ('item_not_exists');
        }

        if ($item->getStatus () == Toluca_PDV_Helper_Data::ITEM_STATUS_OPENED)
        {
            $this->_fault ('item_already_opened');
        }

        $user = Mage::getModel ('pdv/user')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $user_id))
            ->addFieldToFilter ('item_id',   array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$user || !$user->getId ())
        {
            $this->_fault ('user_not_exists');
        }

        $password = Mage::helper ('core')->getHash ($password, true);

        if (strcmp ($password, $user->getPassword ()) != 0)
        {
            $this->_fault ('user_invalid_password');
        }

        $item->setStatus (Toluca_PDV_Helper_Data::ITEM_STATUS_OPENED)
            ->setUserId ($user_id)
            ->setOpenAmount ($amount)
            ->setReinforceAmount (0)
            ->setBleedAmount (0)
            ->setMoneyAmount (0)
            ->setChangeAmount (0)
            ->setCloseAmount (0)
            ->setOpenedAt (date ('c'))
            ->setClosedAt (new Zend_Db_Expr ('NULL'))
            ->save ()
        ;

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_OPEN)
            ->setItemId ($item->getId ())
            ->setUserId ($user->getId ())
            ->setAmount ($amount)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return true;
    }

    public function reinforce ($item_id, $amount, $user_id, $password)
    {
        if (empty ($item_id))
        {
            $this->_fault ('item_not_specified');
        }

        if (empty ($amount))
        {
            $this->_fault ('amount_not_specified');
        }

        if (empty ($user_id))
        {
            $this->_fault ('user_not_specified');
        }

        if (empty ($password))
        {
            $this->_fault ('password_not_specified');
        }

        $item = Mage::getModel ('pdv/item')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$item || !$item->getId ())
        {
            $this->_fault ('item_not_exists');
        }

        if ($item->getStatus () == Toluca_PDV_Helper_Data::ITEM_STATUS_CLOSED)
        {
            $this->_fault ('item_already_closed');
        }

        $reinforceAmount = floatval ($item->getReinforceAmount ());

        $user = Mage::getModel ('pdv/user')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $user_id))
            ->addFieldToFilter ('item_id',   array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$user || !$user->getId ())
        {
            $this->_fault ('user_not_exists');
        }

        $password = Mage::helper ('core')->getHash ($password, true);

        if (strcmp ($password, $user->getPassword ()) != 0)
        {
            $this->_fault ('user_invalid_password');
        }

        $item->setReinforceAmount ($reinforceAmount + $amount)
            ->save ()
        ;

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_REINFORCE)
            ->setItemId ($item->getId ())
            ->setUserId ($user->getId ())
            ->setAmount ($amount)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return true;
    }

    public function bleed ($item_id, $amount, $user_id, $password)
    {
        if (empty ($item_id))
        {
            $this->_fault ('item_not_specified');
        }

        if (empty ($amount))
        {
            $this->_fault ('amount_not_specified');
        }

        if (empty ($user_id))
        {
            $this->_fault ('user_not_specified');
        }

        if (empty ($password))
        {
            $this->_fault ('password_not_specified');
        }

        $item = Mage::getModel ('pdv/item')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$item || !$item->getId ())
        {
            $this->_fault ('item_not_exists');
        }

        if ($item->getStatus () == Toluca_PDV_Helper_Data::ITEM_STATUS_CLOSED)
        {
            $this->_fault ('item_already_closed');
        }

        $openAmount = floatval ($item->getOpenAmount ());
        $reinforceAmount = floatval ($item->getReinforceAmount ());
        $bleedAmount  = floatval ($item->getBleedAmount ());
        $moneyAmount  = floatval ($item->getMoneyAmount ());
        $changeAmount = floatval ($item->getChangeAmount ());

        $closeAmount = ((($openAmount + $reinforceAmount) - $bleedAmount) + $moneyAmount) - $changeAmount;

        if ($amount > $closeAmount)
        {
            $closeAmount = Mage::helper ('core')->currency ($closeAmount, true, false);

            $message = Mage::helper ('pdv')->__('Bleed amount is invalid. Allowed only %s.', $closeAmount);

            $this->_fault ('item_invalid_amount', $message);
        }

        $user = Mage::getModel ('pdv/user')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $user_id))
            ->addFieldToFilter ('item_id',   array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$user || !$user->getId ())
        {
            $this->_fault ('user_not_exists');
        }

        $password = Mage::helper ('core')->getHash ($password, true);

        if (strcmp ($password, $user->getPassword ()) != 0)
        {
            $this->_fault ('user_invalid_password');
        }

        $item->setBleedAmount ($bleedAmount + $amount)
            ->save ()
        ;

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_BLEED)
            ->setItemId ($item->getId ())
            ->setUserId ($user->getId ())
            ->setAmount (- $amount)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return true;
    }

    public function close ($item_id, $amount, $user_id, $password)
    {
        if (empty ($item_id))
        {
            $this->_fault ('item_not_specified');
        }

        if (empty ($amount))
        {
            $this->_fault ('amount_not_specified');
        }

        if (empty ($user_id))
        {
            $this->_fault ('user_not_specified');
        }

        if (empty ($password))
        {
            $this->_fault ('password_not_specified');
        }

        $item = Mage::getModel ('pdv/item')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$item || !$item->getId ())
        {
            $this->_fault ('item_not_exists');
        }

        if ($item->getStatus () == Toluca_PDV_Helper_Data::ITEM_STATUS_CLOSED)
        {
            $this->_fault ('item_already_closed');
        }

        $openAmount      = floatval ($item->getOpenAmount ());
        $reinforceAmount = floatval ($item->getReinforceAmount ());
        $bleedAmount     = floatval ($item->getBleedAmount ());
        $moneyAmount     = floatval ($item->getMoneyAmount ());
        $changeAmount    = floatval ($item->getChangeAmount ());

        $closeAmount = ((($openAmount + $reinforceAmount) - $bleedAmount) + $moneyAmount) - $changeAmount;
        $differenceAmount = $amount - $closeAmount;

        if ($differenceAmount != 0)
        {
            $differenceAmount = Mage::helper ('core')->currency ($differenceAmount, true, false);

            $message = Mage::helper ('pdv')->__('Close amount is invalid. Difference of %s.', $differenceAmount);

            $this->_fault ('item_invalid_amount', $message);
        }

        $user = Mage::getModel ('pdv/user')->getCollection ()
            ->addFieldToFilter ('is_active', array ('eq' => true))
            ->addFieldToFilter ('entity_id', array ('eq' => $user_id))
            ->addFieldToFilter ('item_id',   array ('eq' => $item_id))
            ->getFirstItem ()
        ;

        if (!$user || !$user->getId ())
        {
            $this->_fault ('user_not_exists');
        }

        $password = Mage::helper ('core')->getHash ($password, true);

        if (strcmp ($password, $user->getPassword ()) != 0)
        {
            $this->_fault ('user_invalid_password');
        }

        $item->setStatus (Toluca_PDV_Helper_Data::ITEM_STATUS_CLOSED)
            ->setCloseAmount ($amount)
            ->setClosedAt (date ('c'))
            ->save ()
        ;

        $history = Mage::getModel ('pdv/history')
            ->setTypeId (Toluca_PDV_Helper_Data::HISTORY_TYPE_CLOSE)
            ->setItemId ($item->getId ())
            ->setUserId ($user->getId ())
            ->setAmount (- $amount)
            ->setCreatedAt (date ('c'))
            ->save ()
        ;

        return true;
    }
}

