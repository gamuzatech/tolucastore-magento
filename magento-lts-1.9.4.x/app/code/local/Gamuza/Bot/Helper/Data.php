<?php
/**
 * @package     Gamuza_Bot
 * @copyright   Copyright (c) 2020 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_Bot_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ORDER_ATTRIBUTE_IS_BOT = 'is_bot';
    const ORDER_ATTRIBUTE_BOT_TYPE = 'bot_type';

    const BOT_TYPE_SIGNAL   = 'signal';
    const BOT_TYPE_TELEGRAM = 'telegram';
    const BOT_TYPE_WHATSAPP = 'whatsapp';

    const MESSAGE_TYPE_QUESTION = 'question';
    const MESSAGE_TYPE_ANSWER   = 'answer';

    const STATUS_CATEGORY = 'category';
    const STATUS_PRODUCT  = 'product';
    const STATUS_OPTION   = 'option';
    const STATUS_VALUE    = 'value';
    const STATUS_BUNDLE   = 'bundle';
    const STATUS_SELECTION = 'selection';
    const STATUS_COMMENT  = 'comment';
    const STATUS_CART     = 'cart';
    const STATUS_ADDRESS  = 'address';
    const STATUS_SHIPPING = 'shipping';
    const STATUS_PAYMENT  = 'payment';
    const STATUS_PAYMENT_CASH = 'payment_cash';
    const STATUS_PAYMENT_MACHINE = 'payment_machine';
    const STATUS_PAYMENT_CRIPTO = 'payment_cripto';
    const STATUS_CHECKOUT = 'checkout';
    const STATUS_ORDER    = 'order';
    const STATUS_ZAP      = 'zap';

    const XML_PATH_BOT_BASIC_AUTH_ACTIVE   = 'bot/basic_auth/active';
    const XML_PATH_BOT_BASIC_AUTH_USERNAME = 'bot/basic_auth/username';
    const XML_PATH_BOT_BASIC_AUTH_PASSWORD = 'bot/basic_auth/password';

    public function uniqid ()
    {
        return hash ('sha512', uniqid (rand (), true));
    }
}

