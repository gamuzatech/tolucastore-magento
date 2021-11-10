<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

/**
 * Queue API
 */
class Gamuza_Bot_Model_Queue_Api extends Mage_Api_Model_Resource_Abstract
{
    const CATEGORY_ID_LENGTH = 5;
    const PRODUCT_ID_LENGTH  = 5;
    const OPTION_ID_LENGTH   = 5;
    const VALUE_ID_LENGTH    = 5;
    const SHIPPING_ID_LENGTH = 5;
    const PAYMENT_ID_LENGTH  = 5;
    const CCTYPE_ID_LENGTH   = 5;
    const QUANTITY_LENGTH    = 5;

    const COMMAND_ZERO    = '0';
    const COMMAND_OK      = 'ok';
    const COMMAND_CLEAR   = 'limpar';

    const DEFAULT_CUSTOMER_EMAIL  = 'bot@toluca.com.br';
    const DEFAULT_CUSTOMER_TAXVAT = '123.456.789-01';

    protected $_shippingMethods = array(
        '1'  => 'freeshipping_freeshipping',
        '2'  => 'flatrate_flatrate',
        '3'  => 'tablerate_tablerate',
        '4'  => 'pedroteixeira_correios_40045',
        '5'  => 'pedroteixeira_correios_40215',
        '6'  => 'pedroteixeira_correios_40290',
        '7'  => 'pedroteixeira_correios_04510',
        '8'  => 'pedroteixeira_correios_04014',
        '9'  => 'pedroteixeira_correios_04669',
        '10' => 'pedroteixeira_correios_04162',
        '11' => 'pedroteixeira_correios_04693',
        '12' => 'pedroteixeira_correios_10065',
    );

    protected $_paymentMethods = array(
        '1' => 'cashondelivery',
        '2' => 'machineondelivery',
        '3' => 'banktransfer',
        '4' => 'gamuza_picpay_payment',
        '5' => 'gamuza_blockchain_info',
        '6' => 'purchaseorder',
    );

    protected $_paymentCcTypes = array(
        '1'  => 'AE',
        '2'  => 'AL',
        '3'  => 'AU',
        '4'  => 'BC',
        '5'  => 'CC',
        '6'  => 'DC',
        '7'  => 'DI',
        '8'  => 'EC',
        '9'  => 'ED',
        '10' => 'ELO',
        '11' => 'HI',
        '12' => 'HC',
        '13' => 'JCB',
        '14' => 'MC',
        '15' => 'SM',
        '16' => 'SO',
        '17' => 'TI',
        '18' => 'VI',
        '19' => 'VE',
        '20' => 'VR',
        '21' => 'BTC',
    );

    public function __construct ()
    {
        // parent::__construct ();

        $this->_phone = preg_replace ('[\D]', null, Mage::getStoreConfig ('general/store_information/phone'));
    }

    public function message ($from, $to, $senderName, $senderMessage)
    {
        $from = preg_replace ('[\D]', null, $from);
        $to   = preg_replace ('[\D]', null, $to);

        if (strpos ($to, $this->_phone) === false)
        {
            return $this->_setJsonBody ('[ WRONG NUMBER ]');
        }

        Mage::app ()->setCurrentStore (Mage_Core_Model_App::DISTRO_STORE_ID);

        $storeId = Mage::app ()->getStore ()->getId ();

        $collection = Mage::getModel ('bot/queue')->getCollection ()
            ->addFieldToFilter ('store_id',  array ('eq' => $storeId))
            ->addFieldToFilter ('quote_id',  array ('gt' => 0))
            ->addFieldToFilter ('order_id',  array ('eq' => 0))
            ->addFieldToFilter ('number',    array ('eq' => $from))
            ->addFieldToFilter ('status',    array ('neq' => Gamuza_Bot_Helper_Data::STATUS_ORDER))
        ;

        if (!empty ($senderName))
        {
             $senderName = explode (' ', $senderName, 2);
        }

        if (is_array ($senderName) && count ($senderName) == 1)
        {
            $senderName [1] = '------';
        }

        if (!$senderName || !is_array ($senderName) || count ($senderName) != 2)
        {
            $senderName = array(
                0 => Mage::helper ('bot')->__('Firstname'),
                1 => Mage::helper ('bot')->__('Lastname'),
            );
        }

        $shippingPostcode = preg_replace ('[\D]', null, Mage::getStoreConfig ('shipping/origin/postcode', $storeId));

        $remoteIp = Mage::helper ('core/http')->getRemoteAddr (false);

        if (!$collection->getSize ())
        {
            $quote = Mage::getModel ('sales/quote')
                ->setStoreId ($storeId)
                ->setIsActive (true)
                ->setIsMultiShipping (false)
                ->setRemoteIp ($remoteIp)
                ->setCustomerFirstname ($senderName [0])
                ->setCustomerLastname ($senderName [1])
                ->setCustomerEmail (self::DEFAULT_CUSTOMER_EMAIL)
                ->save ()
            ;

            $queue = Mage::getModel ('bot/queue')
                ->setStoreId ($storeId)
                ->setQuoteId ($quote->getId ())
                ->setNumber ($from)
                ->setFirstname ($senderName [0])
                ->setLastname ($senderName [1])
                ->setRemoteIp ($remoteIp)
                ->setEmail (self::DEFAULT_CUSTOMER_EMAIL)
                ->setStatus (Gamuza_Bot_Helper_Data::STATUS_CATEGORY)
                ->setCreatedAt (date ('c'))
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;

            $customerData = array(
                'mode'      => Mage_Checkout_Model_Type_Onepage::METHOD_GUEST,
                'firstname' => $senderName [0],
                'lastname'  => $senderName [1],
                'email'     => self::DEFAULT_CUSTOMER_EMAIL,
                'taxvat'    => self::DEFAULT_CUSTOMER_TAXVAT,
            );

            Mage::getModel ('checkout/cart_customer_api')->set ($quote->getId (), $customerData, $storeId);

            $quote->setData (Gamuza_Bot_Helper_Data::ORDER_ATTRIBUTE_IS_BOT, true)
                ->setCustomerGroupId (0)
                ->setCustomerIsGuest (1)
                ->save ()
            ;

            Mage::getModel ('checkout/cart_customer_api')->setAddresses ($quote->getId (), array(
                array(
                    'mode'       => 'billing',
                    'firstname'  => $senderName [0],
                    'lastname'   => $senderName [1],
                    'company'    => null,
                    'street'     => array ('x', '0', null, 'y'),
                    'city'       => Mage::getStoreConfig ('shipping/origin/city',      $storeId),
                    'region'     => Mage::getStoreConfig ('shipping/origin/region_id', $storeId),
                    'postcode'   => $shippingPostcode,
                    'country_id' => 'BR',
                    'telephone'  => null,
                    'fax'        => substr ($from, -11),
                    'use_for_shipping' => 1,
                )
            ), $storeId);

            $result = Mage::helper ('bot/message')->getGreetingText (implode (' ', $senderName)) . PHP_EOL . PHP_EOL
                . Mage::helper ('bot/message')->getWelcomeText () . PHP_EOL . PHP_EOL
                . $this->_getCategoryList ($storeId)
            ;

            return $this->_setJsonBody ($result);
        }

        $queue = $collection->getFirstItem ();

        $queue->setRemoteIp ($remoteIp)->save ();

        if ($queue->getIsMuted ())
        {
            return $this->_setJsonBody ('');
        }

        $body = Mage::helper ('core')->removeAccents ($senderMessage);

        $result = null;

        if (!strcmp (strtolower (trim ($body)), Gamuza_Bot_Helper_Data::STATUS_BOT))
        {
            $queue->setIsMuted (true)->save ();

            $result = Mage::helper ('bot/message')->getPleaseWaitAnAttendantText ();

            return $this->_setJsonBody ($result);
        }

        switch ($queue->getStatus ())
        {
            case Gamuza_Bot_Helper_Data::STATUS_CATEGORY:
            {
                $categoryId = intval ($body);

                $collection = $this->_getCategoryCollection ($storeId)
                    ->addFieldToFilter ('main_table.position', array ('eq' => $categoryId))
                ;

                $categoryId = $collection->getFirstItem ()->getId ();

                if ($collection->getSize () > 0)
                {
                    $queue->setCategoryId ($categoryId)
                        ->setStatus (Gamuza_Bot_Helper_Data::STATUS_PRODUCT)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    $result = $this->_getProductList ($storeId, $categoryId);
                }
                else
                {
                    $result = $this->_getCategoryList ($storeId);
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_PRODUCT:
            {
                $info = Mage::getModel ('checkout/cart_api')->info ($queue->getQuoteId (), $storeId);

                if (!strcmp (strtolower (trim ($body)), self::COMMAND_OK) && count ($info ['items']) > 0)
                {
                    $result = $this->_getCartReview ($queue->getQuoteId (), $storeId)
                        . Mage::helper ('bot/message')->getTypeListToCategoriesText (self::COMMAND_ZERO) . PHP_EOL . PHP_EOL
                        . Mage::helper ('bot/message')->getTypeClearToRestartText (self::COMMAND_CLEAR) . PHP_EOL . PHP_EOL
                        . Mage::helper ('bot/message')->getTypeCommandToContinueText (self::COMMAND_OK)
                    ;

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_CART)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }

                if (!strcmp (strtolower (trim ($body)), self::COMMAND_ZERO))
                {
                    $result = $this->_getCategoryList ($storeId);

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_CATEGORY)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }

                $productId = intval ($body);

                $category = Mage::getModel ('catalog/category')->load ($queue->getCategoryId ());

                $collection = $this->_getProductCollection ($storeId, $category)
                    ->addAttributeToFilter ('sku_position', array ('eq' => $productId))
                ;

                $productId = $collection->getFirstItem ()->getId ();

                $product = Mage::getModel ('catalog/product')->load ($productId);

                if ($product && $product->getId () > 0 && in_array ($storeId, $product->getStoreIds ()))
                {
                    $queue->setProductId ($product->getId ())
                        ->save ()
                    ;

                    if (!strcmp ($product->getTypeId (), Mage_Catalog_Model_Product_Type::TYPE_BUNDLE))
                    {
                        $result = $this->_getBundleOptions ($product->getId ());

                        $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_BUNDLE)
                            ->setUpdatedAt (date ('c'))
                            ->save ()
                        ;

                        break;
                    }

                    if ($product->getHasOptions ())
                    {
                        $result = $this->_getProductOptions ($product);

                        $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_OPTION)
                            ->setUpdatedAt (date ('c'))
                            ->save ()
                        ;
                    }
                    else
                    {
                        $productsData = array(
                            array(
                                'product_id' => $queue->getProductId (),
                            )
                        );

                        try
                        {
                            Mage::getModel ('checkout/cart_product_api')->add ($queue->getQuoteId (), $productsData, $storeId);

                            $result = Mage::helper ('bot/message')->getProductAddedToCartText () . PHP_EOL . PHP_EOL
                                . $this->_getProductList ($storeId, $queue->getCategoryId ()) . PHP_EOL . PHP_EOL
                                . Mage::helper ('bot/message')->getTypeCommandToGoToCartText (self::COMMAND_OK);
                            ;

                            $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_PRODUCT)
                                ->setSelections (new Zend_Db_Expr ('NULL'))
                                ->setOptions (new Zend_Db_Expr ('NULL'))
                                ->setComment (new Zend_Db_Expr ('NULL'))
                                ->setUpdatedAt (date ('c'))
                                ->save ()
                            ;
                        }
                        catch (Mage_Api_Exception $e)
                        {
                            $result = Mage::helper ('bot/message')->getProductNotAddedToCartText () . PHP_EOL . PHP_EOL
                                . $e->getCustomMessage () . PHP_EOL . PHP_EOL
                                . $this->_getProductList ($seller->getStoreId, $queue->getCategoryId ())
                            ;

                            $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_PRODUCT)
                                ->setUpdatedAt (date ('c'))
                                ->save ()
                            ;
                        }
                    }
                }
                else
                {
                    $result = $this->_getProductList ($storeId, $queue->getCategoryId ());

                    $info = Mage::getModel ('checkout/cart_api')->info ($queue->getQuoteId (), $storeId);

                    if (count ($info ['items']) > 0)
                    {
                        $result .= PHP_EOL . PHP_EOL . Mage::helper ('bot/message')->getTypeCommandToGoToCartText (self::COMMAND_OK);
                    }
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_BUNDLE:
            {
                if (!strcmp (strtolower (trim ($body)), self::COMMAND_OK))
                {
                    $queueStatus = null;

                    $product = Mage::getModel ('catalog/product')->load ($queue->getProductId ());

                    if ($product->getHasOptions ())
                    {
                        $result = $this->_getProductOptions ($product);

                        $queueStatus = Gamuza_Bot_Helper_Data::STATUS_OPTION;
                    }
                    else
                    {
                        $result = Mage::helper ('bot/message')->getAddCommentForProductText ();

                        $queueStatus = Gamuza_Bot_Helper_Data::STATUS_COMMENT;
                    }

                    $queue->setStatus ($queueStatus)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }

                $optionId = intval ($body);

                $collection = Mage::getModel ('bundle/option')->getCollection ()
                    ->joinValues (Mage_Core_Model_App::ADMIN_STORE_ID)
                    ->setProductIdFilter ($queue->getProductId ())
                    ->addFieldToFilter ('main_table.sort_order', array ('eq' => $optionId))
                ;

                $option = $collection->getFirstItem ();

                if ($option && $option->getId () > 0)
                {
                    $result = $this->_getBundleSelections ($option);

                    $queue->setBundleId ($option->getId ())
                        ->setStatus (Gamuza_Bot_Helper_Data::STATUS_SELECTION)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }
                else
                {
                    $result = $this->_getBundleOptions ($queue->getProductId ());
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_SELECTION:
            {
                preg_match_all ('/([\d]{1,})/', $body, $matches);

                $collection = Mage::getModel ('bundle/selection')->getCollection ()
                    ->addAttributeToFilter ('name', array ('notnull' => true))
                    ->setOptionIdsFilter ($queue->getBundleId ())
                ;

                $collection->getSelect ()
                    ->where ('selection.parent_product_id = ?', $queue->getProductId ())
                    ->where ('selection.sort_order IN (?)', $matches [0])
                    ->reset (Zend_Db_Select::COLUMNS)
                    ->columns (array(
                        'id'   => 'selection.selection_id',
                        'name' => 'e.name'
                    ))
                ;

                if ($collection->count () > 0 || !strcmp (strtolower (trim ($body)), self::COMMAND_OK))
                {
                    $product = Mage::getModel ('catalog/product')->load ($queue->getProductId ());

                    $result = $this->_getBundleOptions ($queue->getProductId ());

                    $productSelections = json_decode ($queue->getSelections (), true);

                    $productSelections [$queue->getBundleId ()] = array_keys ($collection->toOptionHash ());

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_BUNDLE)
                        ->setSelections (json_encode ($productSelections))
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }
                else
                {
                    $collection = Mage::getModel ('bundle/option')->getCollection ()
                        ->setIdFilter ($queue->getBundleId ())
                        ->setProductIdFilter ($queue->getProductId ())
                        ->joinValues (Mage_Core_Model_App::ADMIN_STORE_ID)
                    ;

                    $option = $collection->getFirstItem ();

                    $result = $this->_getBundleSelections ($option);
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_OPTION:
            {
                if (!strcmp (strtolower (trim ($body)), self::COMMAND_OK))
                {
                    $result = Mage::helper ('bot/message')->getAddCommentForProductText ();

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_COMMENT)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }

                $optionId = intval ($body);

                $collection = Mage::getModel ('catalog/product_option')->getCollection ()
                    ->addFieldToFilter ('main_table.product_id', array ('eq' => $queue->getProductId ()))
                    ->addFieldToFilter ('main_table.sort_order', array ('eq' => $optionId))
                    ->addTitleToResult ($storeId)
                    ->addValuesToResult ($storeId)
                ;

                $option = $collection->getFirstItem ();

                if ($option && $option->getId () > 0)
                {
                    $queue->setOptionId ($option->getId ())
                        ->save ()
                    ;

                    $result = $this->_getProductValues ($option);

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_VALUE)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }
                else
                {
                    $product = Mage::getModel ('catalog/product')->load ($queue->getProductId ());

                    $result = $this->_getProductOptions ($product);
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_VALUE:
            {
                preg_match_all ('/([\d]{1,})/', $body, $matches);

                $collection = Mage::getModel ('catalog/product_option_value')->getCollection ()
                    ->addFieldToFilter ('main_table.sort_order', array ('in' => $matches [0]))
                    ->addTitleToResult ($storeId)
                    ->addPriceToResult ($storeId)
                    ->addOptionToFilter ($queue->getOptionId ())
                    // ->getValuesByOption ($matches [0])
                ;

                $collection->getSelect ()
                    ->reset (Zend_Db_Select::COLUMNS)
                    ->columns (array(
                        'id'   => 'main_table.option_type_id',
                        'name' => 'default_value_title.title'
                    ))
                ;

                if ($collection->count () > 0 || !strcmp (strtolower (trim ($body)), self::COMMAND_OK))
                {
                    $product = Mage::getModel ('catalog/product')->load ($queue->getProductId ());

                    $result = $this->_getProductOptions ($product);

                    $productOptions = json_decode ($queue->getOptions (), true);

                    $productOptions [$queue->getOptionId ()] = array_keys ($collection->toOptionHash ());

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_OPTION)
                        ->setOptions (json_encode ($productOptions))
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }
                else
                {
                    $collection = Mage::getModel ('catalog/product_option')->getCollection ()
                        ->addFieldToFilter ('main_table.option_id', array ('eq' => $queue->getOptionId ()))
                        ->addTitleToResult ($storeId)
                        ->addValuesToResult ($storeId)
                    ;

                    $option = $collection->getFirstItem ();

                    $result = $this->_getProductValues ($option);
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_COMMENT:
            {
                $additionalOptions = null;

                if (strcmp (strtolower (trim ($body)), self::COMMAND_OK) != 0)
                {
                    $additionalOptions = array(
                        array(
                            'code'  => 'comment',
                            'label' => Mage::helper ('bot')->__('Comment'),
                            'value' => $body,
                        )
                    );
                }

                $productsData = array(
                    array(
                        'product_id'         => $queue->getProductId (),
                        'bundle_option'      => json_decode ($queue->getSelections (), true),
                        'options'            => json_decode ($queue->getOptions (), true),
                        'additional_options' => $additionalOptions,
                    )
                );

                try
                {
                    Mage::getModel ('checkout/cart_product_api')->add ($queue->getQuoteId (), $productsData, $storeId);

                    $result = Mage::helper ('bot/message')->getProductAddedToCartText () . PHP_EOL . PHP_EOL
                        . $this->_getProductList ($storeId, $queue->getCategoryId ()) . PHP_EOL . PHP_EOL
                        . Mage::helper ('bot/message')->getTypeCommandToGoToCartText (self::COMMAND_OK);
                    ;

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_PRODUCT)
                        ->setComment ($body)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }
                catch (Mage_Api_Exception $e)
                {
                    $result = Mage::helper ('bot/message')->getProductNotAddedToCartText () . PHP_EOL . PHP_EOL
                        . Mage::helper ('bot')->__('Obs: %s', $e->getCustomMessage ()) . PHP_EOL . PHP_EOL
                        . $this->_getProductList ($seller->getStoreId, $queue->getCategoryId ())
                    ;

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_PRODUCT)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_CART:
            {
                if (!strcmp (strtolower (trim ($body)), self::COMMAND_ZERO))
                {
                    $result = $this->_getCategoryList ($storeId);

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_CATEGORY)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }

                if (!strcmp (strtolower (trim ($body)), self::COMMAND_CLEAR))
                {
                    $quote = Mage::getModel ('sales/quote')->load ($queue->getQuoteId ());

                    foreach ($quote->getAllItems () as $item)
                    {
                        $item->delete ();
                    }

                    $result = $this->_getCategoryList ($storeId);

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_CATEGORY)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }

                if (!strcmp (strtolower (trim ($body)), self::COMMAND_OK))
                {
                    $result = Mage::helper ('bot/message')->getPleaseEnterTheAddressText ();

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_ADDRESS)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;

                    break;
                }

                $result = $this->_getCartReview ($queue->getQuoteId (), $storeId)
                    . Mage::helper ('bot/message')->getTypeListToCategoriesText (self::COMMAND_ZERO) . PHP_EOL . PHP_EOL
                    . Mage::helper ('bot/message')->getTypeClearToRestartText (self::COMMAND_CLEAR) . PHP_EOL . PHP_EOL
                    . Mage::helper ('bot/message')->getTypeCommandToContinueText (self::COMMAND_OK)
                ;

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_ADDRESS:
            {
                $street = Mage::helper ('core')->removeAccents ($body);

                if (preg_match ('/(.*)\s([\d]{1,})\s(.*)/', $street, $matches) == '1')
                {
                    Mage::getModel ('checkout/cart_customer_api')->setAddresses ($queue->getQuoteId (), array(
                        array(
                            'mode'       => 'billing',
                            'firstname'  => $senderName [0],
                            'lastname'   => $senderName [1],
                            'company'    => null,
                            'street'     => array ($matches [1], $matches [2], null, $matches [3]),
                            'city'       => Mage::getStoreConfig ('shipping/origin/city', $storeId),
                            'region'     => Mage::getStoreConfig ('shipping/origin/region_id', $storeId),
                            'postcode'   => $shippingPostcode,
                            'country_id' => 'BR',
                            'telephone'  => null,
                            'fax'        => substr ($queue->getNumber (), -11),
                            'use_for_shipping' => 1,
                        )
                    ), $storeId);

                    $shippingMethods = Mage::getModel ('checkout/cart_shipping_api')->getShippingMethodsList ($queue->getQuoteId (), $storeId);

                    if (count ($shippingMethods) > 0)
                    {
                        $result = Mage::helper ('bot/message')->getEnterDeliveryMethodText () . PHP_EOL . PHP_EOL;

                        foreach ($shippingMethods as $method)
                        {
                            foreach ($this->_shippingMethods as $_id => $_method)
                            {
                                if (!strcmp ($method ['code'], $_method))
                                {
                                    $strLen = self::SHIPPING_ID_LENGTH - strlen ($_id);
                                    $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

                                    $shippingPrice = Mage::helper ('core')->currency ($method ['price'], true, false);

                                    $result .= sprintf ("*%s*%s%s *%s*", $_id, $strPad, $method ['method_title'], $shippingPrice) . PHP_EOL;
                                }
                            }
                        }

                        $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_SHIPPING)
                            ->setUpdatedAt (date ('c'))
                            ->save ()
                        ;
                    }
                    else
                    {
                        $result = Mage::helper ('bot/message')->getNoDeliveryMethodFoundText () . PHP_EOL . PHP_EOL;

                        $result .= Mage::helper ('bot/message')->getPleaseEnterTheAddressText ();
                    }
                }
                else
                {
                    $result = Mage::helper ('bot/message')->getPleaseEnterTheAddressText ();
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_SHIPPING:
            {
                $shippingId = intval ($body);

                $shippingMethods = Mage::getModel ('checkout/cart_shipping_api')->getShippingMethodsList ($queue->getQuoteId (), $storeId);

                foreach ($shippingMethods as $id => $method)
                {
                    if (!in_array ($method ['code'], $this->_shippingMethods))
                    {
                        unset ($shippingMethods [$id]);
                    }
                }

                if (!empty ($this->_shippingMethods [$shippingId]) && $this->_getAllowedShipping ($shippingMethods, $shippingId))
                {
                    Mage::getModel ('checkout/cart_shipping_api')->setShippingMethod ($queue->getQuoteId (), $this->_shippingMethods [$shippingId], $storeId);

                    $paymentMethods = Mage::getModel ('checkout/cart_payment_api')->getPaymentMethodsList ($queue->getQuoteId (), $storeId);

                    if (count ($paymentMethods) > 0)
                    {
                        $result = Mage::helper ('bot/message')->getEnterPaymentMethodText () . PHP_EOL . PHP_EOL;

                        $quote = Mage::getModel ('sales/quote')->load ($queue->getQuoteId ());

                        foreach ($paymentMethods as $method)
                        {
                            foreach ($this->_paymentMethods as $_id => $_method)
                            {
                                if (!strcmp ($method ['code'], $_method))
                                {
                                    $strLen = self::PAYMENT_ID_LENGTH - strlen ($_id);
                                    $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

                                    $paymentPrice = Mage::helper ('core')->currency ($quote->getBaseGrandTotal (), true, false);

                                    $result .= sprintf ("*%s*%s%s *%s*", $_id, $strPad, $method ['title'], $paymentPrice) . PHP_EOL;
                                }
                            }
                        }

                        $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_PAYMENT)
                            ->setUpdatedAt (date ('c'))
                            ->save ()
                        ;
                    }
                    else
                    {
                        $result = Mage::helper ('bot/message')->getNoPaymentMethodFoundText () . PHP_EOL . PHP_EOL
                            . Mage::helper ('bot/message')->getEnterDeliveryMethodText () . PHP_EOL . PHP_EOL
                        ;

                        foreach ($shippingMethods as $method)
                        {
                            foreach ($this->_shippingMethods as $_id => $_method)
                            {
                                if (!strcmp ($method ['code'], $_method))
                                {
                                    $strLen = self::SHIPPING_ID_LENGTH - strlen ($_id);
                                    $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

                                    $shippingPrice = Mage::helper ('core')->currency ($method ['price'], true, false);

                                    $result .= sprintf ("*%s*%s%s *%s*", $_id, $strPad, $method ['method_title'], $shippingPrice) . PHP_EOL;
                                }
                            }
                        }
                    }
                }
                else
                {
                    $result = Mage::helper ('bot/message')->getEnterDeliveryMethodText () . PHP_EOL . PHP_EOL;

                    foreach ($shippingMethods as $method)
                    {
                        foreach ($this->_shippingMethods as $_id => $_method)
                        {
                            if (!strcmp ($method ['code'], $_method))
                            {
                                $strLen = self::SHIPPING_ID_LENGTH - strlen ($_id);
                                $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

                                $shippingPrice = Mage::helper ('core')->currency ($method ['price'], true, false);

                                $result .= sprintf ("*%s*%s%s *%s*", $_id, $strPad, $method ['method_title'], $shippingPrice) . PHP_EOL;
                            }
                        }
                    }
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_PAYMENT:
            {
                $paymentId = intval ($body);

                $paymentMethods = Mage::getModel ('checkout/cart_payment_api')->getPaymentMethodsList ($queue->getQuoteId (), $storeId);

                foreach ($paymentMethods as $id => $method)
                {
                    if (!in_array ($method ['code'], $this->_paymentMethods))
                    {
                        unset ($paymentMethods [$id]);
                    }
                }

                if (!empty ($this->_paymentMethods [$paymentId]) && $this->_getAllowedPayment ($paymentMethods, $paymentId))
                {
                    $queueStatus = Gamuza_Bot_Helper_Data::STATUS_CHECKOUT;

                    switch ($this->_paymentMethods [$paymentId])
                    {
                        case 'cashondelivery':
                        {
                            $result = Mage::helper ('bot/message')->getEnterAmountForMoneyChangeText () . PHP_EOL . PHP_EOL
                                . Mage::helper ('bot/message')->getTypeCommandToContinueText (self::COMMAND_OK)
                            ;

                            $queueStatus = Gamuza_Bot_Helper_Data::STATUS_PAYMENT_CASH;

                            break;
                        }
                        case 'machineondelivery':
                        {
                            $result = $this->_getCardList ($queue->getQuoteId (), $storeId);

                            $queueStatus = Gamuza_Bot_Helper_Data::STATUS_PAYMENT_MACHINE;

                            break;
                        }
                        default:
                        {
                            $paymentData = array(
                                'method' => $this->_paymentMethods [$paymentId]
                            );

                            Mage::getModel ('checkout/cart_payment_api')->setPaymentMethod ($queue->getQuoteId (), $paymentData, $storeId);

                            $result = $this->_getCheckoutReview ($queue->getQuoteId (), $storeId) . PHP_EOL . PHP_EOL;

                            break;
                        }
                    }

                    $queue->setStatus ($queueStatus)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }
                else
                {
                    $result = Mage::helper ('bot/message')->getEnterPaymentMethodText () . PHP_EOL . PHP_EOL;

                    $quote = Mage::getModel ('sales/quote')->load ($queue->getQuoteId ());

                    foreach ($paymentMethods as $method)
                    {
                        foreach ($this->_paymentMethods as $_id => $_method)
                        {
                            if (!strcmp ($method ['code'], $_method))
                            {
                                $strLen = self::PAYMENT_ID_LENGTH - strlen ($_id);
                                $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

                                $paymentPrice = Mage::helper ('core')->currency ($quote->getBaseGrandTotal (), true, false);

                                $result .= sprintf ("*%s*%s%s *%s*", $_id, $strPad, $method ['title'], $paymentPrice) . PHP_EOL;
                            }
                        }
                    }
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_PAYMENT_CASH:
            {
                $paymentChange  = intval ($body);
                $paymentCommand = strtolower (trim ($body));

                $quote = Mage::getModel ('sales/quote')->load ($queue->getQuoteId ());

                if ($paymentChange > $quote->getBaseGrandTotal () || !strcmp ($paymentCommand, self::COMMAND_OK))
                {
                    $paymentData = array(
                        'method'      => 'cashondelivery',
                        'change_type' => $paymentChange ? '1' : '0',
                        'cash_amount' => $paymentChange,
                    );

                    Mage::getModel ('checkout/cart_payment_api')->setPaymentMethod ($queue->getQuoteId (), $paymentData, $storeId);

                    $result = $this->_getCheckoutReview ($queue->getQuoteId (), $storeId) . PHP_EOL . PHP_EOL;

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_CHECKOUT)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }
                else
                {
                    $result = Mage::helper ('bot/message')->getEnterAmountForMoneyChangeText () . PHP_EOL . PHP_EOL
                        . Mage::helper ('bot/message')->getTypeCommandToContinueText (self::COMMAND_OK)
                    ;
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_PAYMENT_MACHINE:
            {
                $paymentId = intval ($body);

                $paymentMethods = Mage::getModel ('checkout/cart_payment_api')->getPaymentMethodsList ($queue->getQuoteId (), $storeId);

                foreach ($paymentMethods as $id => $method)
                {
                    if (strcmp ($method ['code'], 'machineondelivery') != 0)
                    {
                        unset ($paymentMethods [$id]);
                    }
                }

                if (!empty ($this->_paymentCcTypes [$paymentId]) && $this->_getAllowedCcType ($paymentMethods, $paymentId))
                {
                    $paymentData = array(
                        'method'  => 'machineondelivery',
                        'cc_type' => $this->_paymentCcTypes [$paymentId],
                    );

                    Mage::getModel ('checkout/cart_payment_api')->setPaymentMethod ($queue->getQuoteId (), $paymentData, $storeId);

                    $result = $this->_getCheckoutReview ($queue->getQuoteId (), $storeId) . PHP_EOL . PHP_EOL;

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_CHECKOUT)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }
                else
                {
                    $result = $this->_getCardList ($queue->getQuoteId (), $storeId);
                }

                break;
            }
            case Gamuza_Bot_Helper_Data::STATUS_CHECKOUT:
            {
                if (!strcmp (strtolower (trim ($body)), self::COMMAND_OK))
                {
                    Mage::app ()->getStore ()->setConfig (Mage_Checkout_Helper_Data::XML_PATH_GUEST_CHECKOUT, '1');

                    try
                    {
                        $incrementId = Mage::getModel ('checkout/cart_api')->createOrder ($queue->getQuoteId (), $storeId);
                    }
                    catch (Exception $e)
                    {
                        $result = $e->getMessage ();

                        break;
                    }

                    $storeName = Mage::getStoreConfig (Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);

                    $result = Mage::helper ('bot/message')->getYourOrderNumberText ($incrementId) . PHP_EOL . PHP_EOL
                        . Mage::helper ('bot/message')->getOrderInformationText ($incrementId)
                        . Mage::helper ('bot/message')->getThankYouForShoppingText ($storeName) . PHP_EOL . PHP_EOL
                        . Mage::helper ('bot/message')->getBuyThroughTheAppText ()
                    ;

                    $order = Mage::getModel ('sales/order')->loadByIncrementId ($incrementId);

                    $queue->setStatus (Gamuza_Bot_Helper_Data::STATUS_ORDER)
                        ->setOrderId ($order->getId ())
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }
                else
                {
                    $result = $this->_getCheckoutReview ($queue->getQuoteId (), $storeId);
                }

                break;
            }
            default:
            {
                $result = '[ WHERE I AM? ]';

                break;
            }
        }

        return $this->_setJsonBody ($result);
    }

    private function _getCategoryCollection ($storeId)
    {
        $websiteId = Mage::app ()->getStore ($storeId)->getWebsite ()->getId ();

        $collection = Mage::getModel ('catalog/category')->getCollection ()
            ->addIsActiveFilter ()
            ->addNameToResult ()
            ->addFieldToFilter ('level', array ('gteq' => '2'))
        ;

        $collection->getSelect ()
            ->where ('main_table.is_active = 1')
            ->group ('main_table.entity_id')
            ->order ('main_table.position')
            ->join(
                array ('ccp' => Mage::getSingleton ('core/resource')->getTableName ('catalog_category_product')),
                'main_table.entity_id = ccp.category_id',
                array(
                    'products_count' => 'COUNT(ccp.product_id)',
                )
            )
            ->join(
                array ('cpf' => Mage::getSingleton ('core/resource')->getTableName ('catalog_product_flat_' . $storeId)),
                'ccp.product_id = cpf.entity_id',
                array ()
            )
            ->where ('cpf.status = ?',     Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->where ('cpf.visibility = ?', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->join(
                array ('ciss' => Mage::getSingleton ('core/resource')->getTableName ('cataloginventory_stock_status')),
                'cpf.entity_id = ciss.product_id AND ciss.stock_status = 1',
                array ()
            )
            ->where ('ciss.website_id = ?', $websiteId)
        ;

        return $collection;
    }

    private function _getCategoryList ($storeId)
    {
        $result = Mage::helper ('bot/message')->getEnterCategoryCodeText () . PHP_EOL . PHP_EOL;

        $collection = $this->_getCategoryCollection ($storeId);

        foreach ($collection as $category)
        {
            $strLen = self::CATEGORY_ID_LENGTH - strlen ($category->getPosition ());
            $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

            $result .= sprintf ('*%s*%s%s', $category->getPosition (), $strPad, $category->getName ()) . PHP_EOL;
        }

        $result .= PHP_EOL . Mage::helper ('bot/message')->getEnterBotToAttendantText ();

        return $result;
    }

    private function _getProductCollection ($storeId, $category)
    {
        $websiteId = Mage::app ()->getStore ($storeId)->getWebsite ()->getId ();

        $collection = Mage::getModel ('catalog/product')->getCollection ()
            ->addAttributeToSelect ('name')
            ->addAttributeToSelect ('price')
            ->addAttributeToSelect ('special_price')
            ->addAttributeToSelect ('special_from_date')
            ->addAttributeToSelect ('special_to_date')
            ->addAttributeToSelect ('sku_position')
            ->addCategoryFilter ($category)
            ->setVisibility (Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->addFinalPrice ()
        ;

        $collection->getSelect ()
            ->join(
                array ('ciss' => Mage::getSingleton ('core/resource')->getTableName ('cataloginventory_stock_status')),
                'e.entity_id = ciss.product_id AND ciss.stock_status = 1',
                array ()
            )
            ->where ('ciss.website_id = ?', $websiteId)
        ;

        return $collection;
    }

    private function _getProductList ($storeId, $categoryId)
    {
        $category = Mage::getModel ('catalog/category')->load ($categoryId);

        $result = Mage::helper ('bot/message')->getProductsForCategoryText ($category->getName ()) . PHP_EOL . PHP_EOL
            . Mage::helper ('bot/message')->getEnterProductCodeToCartText () . PHP_EOL . PHP_EOL
        ;

        $collection = $this->_getProductCollection ($storeId, $category);

        foreach ($collection as $product)
        {
            $strLen = self::PRODUCT_ID_LENGTH - strlen ($product->getSkuPosition ());
            $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

            if (!floatval ($product->getFinalPrice ()))
            {
                $product->setData ('final_price', $product->getData ('price'));
            }

            $productPrice = Mage::helper ('core')->currency ($product->getFinalPrice (), true, false);

            $result .= sprintf ('*%s*%s%s *%s*', $product->getSkuPosition (), $strPad, $product->getName (), $productPrice) . PHP_EOL;
        }

        $result .= PHP_EOL . Mage::helper ('bot/message')->getTypeListToCategoriesText (self::COMMAND_ZERO);

        return $result;
    }

    private function _getBundleOptions ($productId)
    {
        $result = Mage::helper ('bot/message')->getChooseOptionForProductText () . PHP_EOL . PHP_EOL
            . Mage::helper ('bot/message')->getEnterProductOptionCodeText () . PHP_EOL . PHP_EOL
        ;

        $collection = Mage::getModel ('bundle/option')->getCollection ()
            ->setProductIdFilter ($productId)
            ->joinValues (Mage_Core_Model_App::ADMIN_STORE_ID)
            ->setPositionOrder ()
        ;

        foreach ($collection as $option)
        {
            $strLen = self::OPTION_ID_LENGTH - strlen ($option->getSortOrder ());
            $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

            $required = $option->getRequired () ? sprintf (' *(%s)* ', Mage::helper ('bot')->__('required')) : null;

            $result .= sprintf ('*%s*%s%s%s', $option->getSortOrder (), $strPad, $option->getDefaultTitle (), $required) . PHP_EOL;
        }

        $result .= PHP_EOL . Mage::helper ('bot/message')->getTypeCommandToContinueText (self::COMMAND_OK);

        return $result;
    }

    private function _getBundleSelections ($option)
    {
        if (!strcmp ($option->getType (), 'checkbox'))
        {
            $result = Mage::helper ('bot/message')->getEnterValuesCodesToOptionText ($option->getTitle ()) . PHP_EOL . PHP_EOL;
        }
        else if (!strcmp ($option->getType (), 'select'))
        {
            $result = Mage::helper ('bot/message')->getEnterOneValueCodeToOptionText ($option->getTitle ()) . PHP_EOL . PHP_EOL;
        }

        $collection = Mage::getModel ('bundle/selection')->getCollection ()
            ->addAttributeToFilter ('name', array ('notnull' => true))
            ->addAttributeToSelect ('price')
            ->setOptionIdsFilter ($option->getId ())
            ->setPositionOrder ()
        ;

        foreach ($collection as $selection)
        {
            $strLen = self::VALUE_ID_LENGTH - strlen ($selection->getSortOrder ());
            $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

            $selectionPrice = Mage::helper ('core')->currency ($selection->getFinalPrice (), true, false);

            $result .= sprintf ('*%s*%s%s *%s*', $selection->getSortOrder (), $strPad, $selection->getName (), $selectionPrice) . PHP_EOL;
        }

        $result .= PHP_EOL . Mage::helper ('bot/message')->getTypeCommandToContinueText (self::COMMAND_OK);

        return $result;
    }

    private function _getProductOptions ($product)
    {
        $result = Mage::helper ('bot/message')->getChooseOptionForProductText () . PHP_EOL . PHP_EOL
            . Mage::helper ('bot/message')->getEnterProductOptionCodeText () . PHP_EOL . PHP_EOL
        ;

        foreach ($product->getOptions () as $option)
        {
            $strLen = self::OPTION_ID_LENGTH - strlen ($option->getSortOrder ());
            $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

            $require = $option->getIsRequire () ? sprintf (' *(%s)* ', Mage::helper ('bot')->__('required')) : null;

            $result .= sprintf ('*%s*%s%s%s', $option->getSortOrder (), $strPad, $option->getTitle (), $require) . PHP_EOL;
        }

        $result .= PHP_EOL . Mage::helper ('bot/message')->getTypeCommandToContinueText (self::COMMAND_OK);

        return $result;
    }

    private function _getProductValues ($option)
    {
        if (!strcmp ($option->getType (), 'checkbox'))
        {
            $result = Mage::helper ('bot/message')->getEnterValuesCodesToOptionText ($option->getTitle ()) . PHP_EOL . PHP_EOL;
        }
        else if (!strcmp ($option->getType (), 'drop_down'))
        {
            $result = Mage::helper ('bot/message')->getEnterOneValueCodeToOptionText ($option->getTitle ()) . PHP_EOL . PHP_EOL;
        }

        foreach ($option->getValues () as $value)
        {
            $strLen = self::VALUE_ID_LENGTH - strlen ($value->getSortOrder ());
            $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

            $valuePrice = Mage::helper ('core')->currency ($value->getPrice (), true, false);

            $result .= sprintf ('*%s*%s%s *%s*', $value->getSortOrder (), $strPad, $value->getTitle (), $valuePrice) . PHP_EOL;
        }

        $result .= PHP_EOL . Mage::helper ('bot/message')->getTypeCommandToContinueText (self::COMMAND_OK);

        return $result;
    }

    private function _getCartReview ($quoteId, $storeId)
    {
        $result = Mage::helper ('bot/message')->getThisIsYourShoppingCartText () . PHP_EOL . PHP_EOL;

        $quote = Mage::getModel ('sales/quote')->load ($quoteId);

        foreach ($quote->getAllVisibleItems () as $item)
        {
            $strLen = self::QUANTITY_LENGTH - strlen ($item ['qty'] . 'x');
            $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

            $itemRowTotal = Mage::helper ('core')->currency ($item ['row_total'], true, false);

            $result .= sprintf ('*%s*%s%s *%s*', $item ['qty'] . 'x', $strPad, $item ['name'], $itemRowTotal) . PHP_EOL . PHP_EOL;

            $itemBundleOption = $item->getBuyRequest ()->getData ('bundle_option');

            foreach ($itemBundleOption as $itemBundleOptionId => $itemBundleOptionValues)
            {
                $itemBundleOptionCollection = $item->getProduct ()->getTypeInstance (true)->getOptionsCollection ($item->getProduct ());

                foreach ($itemBundleOptionCollection as $option)
                {
                    if ($option->getId () == $itemBundleOptionId)
                    {
                        $result .= sprintf ('*%s*: ', $option->getDefaultTitle ());

                        $itemBundleSelectionsTitles = array ();

                        $itemBundleSelectionsCollection = $item->getProduct ()->getTypeInstance (true)->getSelectionsCollection (array ($option->getId ()), $item->getProduct ());

                        foreach ($itemBundleSelectionsCollection as $selection)
                        {
                            if (in_array ($selection->getSelectionId (), $itemBundleOptionValues))
                            {
                                $selectionPrice = Mage::helper ('core')->currency ($selection->getPrice (), true, false);

                                $itemBundleSelectionsTitles [] = sprintf ('%s *%s*', $selection->getName (), $selectionPrice);
                            }
                        }

                        $result .= implode (', ', $itemBundleSelectionsTitles) . PHP_EOL . PHP_EOL;
                    }
                }
            }

            $itemOptions = $item->getBuyRequest ()->getData ('options');

            foreach ($itemOptions as $itemOptionId => $itemOptionValues)
            {
                foreach ($item->getProduct ()->getOptions () as $option)
                {
                    if ($option->getOptionId () == $itemOptionId)
                    {
                        $result .= sprintf ('*%s*: ', $option->getDefaultTitle ());

                        $itemOptionValuesTitles = array ();

                        foreach ($option->getValues() as $value)
                        {
                            if (in_array ($value->getOptionTypeId (), $itemOptionValues))
                            {
                                $itemOptionValuesTitles [] = $value->getDefaultTitle ();
                            }
                        }

                        $result .= implode (', ', $itemOptionValuesTitles) . PHP_EOL . PHP_EOL;
                    }
                }
            }

            $itemAdditionalOptions = $item->getBuyRequest ()->getData ('additional_options');

            foreach ($itemAdditionalOptions as $additionalOption)
            {
                $result .= sprintf ('*%s*: %s', $additionalOption ['label'], $additionalOption ['value']) . PHP_EOL . PHP_EOL;
            }
        }

        return $result;
    }

    private function _getCheckoutReview ($quoteId, $storeId)
    {
        $result = $this->_getCartReview ($quoteId, $storeId);

        $info = Mage::getModel ('checkout/cart_api')->info ($quoteId, $storeId);

        $result .= sprintf ('*%s*: %s', Mage::helper ('bot')->__('Shipping Address'), implode (' ', explode (PHP_EOL, $info ['shipping_address']['street'])))
            . PHP_EOL . PHP_EOL
        ;

        $shippingDescription = $info ['shipping_address']['shipping_description'];
        $shippingAmount      = Mage::helper ('core')->currency ($info ['shipping_address']['shipping_amount'], true, false);

        $result .= sprintf ('*%s*: %s *%s*', Mage::helper ('bot')->__('Shipping Method'), $shippingDescription, $shippingAmount)
            . PHP_EOL . PHP_EOL
        ;

        $paymentMethod = $info ['payment']['method'];
        $paymentTitle  = Mage::getStoreconfig ("payment/{$paymentMethod}/title", $storeId);

        $grandTotal = Mage::helper ('core')->currency ($info ['shipping_address']['grand_total'], true, false);

        $result .= sprintf ('*%s*: %s  *%s*', Mage::helper ('bot')->__('Payment Method'), $paymentTitle, $grandTotal)
            . PHP_EOL . PHP_EOL
        ;

        switch ($paymentMethod)
        {
            case 'cashondelivery':
            {
                $paymentChange = $info ['payment']['additional_information']['change_type'];
                $paymentCash   = $info ['payment']['additional_information']['cash_amount'];

                $result .= Mage::helper ('bot/message')->getNeedChangeForMoneyText ($paymentChange, $paymentCash) . PHP_EOL . PHP_EOL;

                break;
            }
            case 'machineondelivery':
            {
                $paymentCcType = $info ['payment']['cc_type'];

                $result .= Mage::helper ('bot/message')->getCardTypeForMachineText ($paymentCcType) . PHP_EOL . PHP_EOL;

                break;
            }
        }

        $result .= Mage::helper ('bot/message')->getEnterToConfirmOrderText ();

        return $result;
    }

    private function _getAllowedShipping ($shippingMethods, $shippingId)
    {
        foreach ($shippingMethods as $method)
        {
            if (!strcmp ($method ['code'], $this->_shippingMethods [$shippingId]))
            {
                return true;
            }
        }

        return false;
    }

    private function _getAllowedPayment ($paymentMethods, $paymentId)
    {
        foreach ($paymentMethods as $method)
        {
            if (!strcmp ($method ['code'], $this->_paymentMethods [$paymentId]))
            {
                return true;
            }
        }

        return false;
    }

    private function _getAllowedCcType ($paymentMethods, $paymentId)
    {
        foreach ($paymentMethods as $method)
        {
            if (!strcmp ($method ['code'], 'machineondelivery'))
            {
                foreach ($method ['cc_types'] as $id => $cctype)
                {
                    if (!strcmp ($id, $this->_paymentCcTypes [$paymentId]))
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function _getCardList ($quoteId, $storeId)
    {
        $paymentMethods = Mage::getModel ('checkout/cart_payment_api')->getPaymentMethodsList ($quoteId, $storeId);

        foreach ($paymentMethods as $paymentId => $paymentValue)
        {
            if (!strcmp ($paymentValue ['code'], 'machineondelivery'))
            {
                foreach ($paymentValue ['cc_types'] as $id => $cctype)
                {
                    if (!in_array ($id, $this->_paymentCcTypes))
                    {
                        unset ($paymentValue ['cc_types'][$id]);
                    }
                }

                $result = Mage::helper ('bot/message')->getChooseTypeOfCardText () . PHP_EOL . PHP_EOL;

                foreach ($this->_paymentCcTypes as $id => $cctype)
                {
                    foreach ($paymentValue ['cc_types'] as $_id => $_cctype)
                    {
                        if (!strcmp ($cctype, $_id))
                        {
                            $strLen = self::CCTYPE_ID_LENGTH - strlen ($id);
                            $strPad = str_pad ("", $strLen, ' ', STR_PAD_RIGHT);

                            $result .= sprintf ("*%s*%s%s", $id, $strPad, $_cctype) . PHP_EOL;
                        }
                    }
                }

                return $result;
            }
        }
    }

    private function _setJsonBody ($body)
    {
        $json = array(
            'result' => $body
        );
        /*
        $this->getResponse()
            ->clearHeaders ()
            ->setHeader ('Content-type','application/json',true)
            ->setBody (Mage::helper('core')->jsonEncode ($json))
        ;
        */
        return $json;
    }
}

