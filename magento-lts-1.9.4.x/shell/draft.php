<?php

// Change current directory to the directory of current script
chdir(dirname(__FILE__));

require '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';

if (!Mage::isInstalled())
{
    echo 'Application is not installed yet, please complete install wizard first.';

    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

try
{
    Mage::app('admin')->setUseSessionInUrl(false);
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;

    exit;
}

umask(0);

try
{
    if ($argc != 2) exit(1);

    Mage::app()->getTranslator()->init(Mage_Core_Model_App_Area::AREA_ADMINHTML, true);

    $order = Mage::getModel('sales/order')->loadByIncrementId($argv[1]);

    ob_start(null, 0, PHP_OUTPUT_HANDLER_STDFLAGS | PHP_OUTPUT_HANDLER_CLEANABLE);

    echo Mage::helper('sales')->__('Order # %s', $order->getRealOrderId()) . PHP_EOL;
    echo Mage::helper('sales')->__('Total Qty: %s', intval($order->getTotalQtyOrdered())) . PHP_EOL;
    echo Mage::helper('core')->formatDate($order->getCreatedAtDate(), 'medium', true) . PHP_EOL;
    echo str_repeat('-', 30) . PHP_EOL;

    $address = $order->getShippingAddress() ? $order->getShippingAddress() : $order->getBillingAddress();

    echo $address->getFirstname() . PHP_EOL;
    echo $address->getFax() . PHP_EOL;
    echo sprintf('%s %s', $address->getStreet1(), $address->getStreet2()) . PHP_EOL;
    if ($address->getStreet3()) echo $address->getStreet3() . PHP_EOL;
    echo $address->getStreet4() . PHP_EOL;
    echo str_repeat('-', 30) . PHP_EOL;

    foreach ($order->getAllItems() as $item)
    {
        echo $item->getSku() . PHP_EOL;
        echo $item->getName() . PHP_EOL;

        $productOptions = $item->getProductOptions();

        foreach ($productOptions ['options'] as $option)
        {
            echo sprintf('%s: %s', $option ['label'], $option ['value']) . PHP_EOL;
        }

        foreach ($productOptions ['additional_options'] as $option)
        {
            echo sprintf('%s: %s', $option ['label'], $option ['value']) . PHP_EOL;
        }

        $price = Mage::helper('core')->currency($item->getPrice(), true, false);
        $qty   = intval($item->getQtyOrdered());

        echo Mage::helper('sales')->__('Price: %s', $price) . PHP_EOL;
        echo Mage::helper('sales')->__('Qty: %s', $qty) . PHP_EOL;

        $rowTotal = Mage::helper('core')->currency($item->getBaseRowTotal(), true, false);

        echo Mage::helper('sales')->__('Total: %s', $rowTotal) . PHP_EOL;

        echo str_repeat('-', 30) . PHP_EOL;
    }

    $subtotal = Mage::helper('core')->currency($order->getBaseSubtotal(), true, false);
    $shipping = Mage::helper('core')->currency($order->getBaseShippingAmount(), true, false);
    $total    = Mage::helper('core')->currency($order->getBaseGrandTotal(), true, false);

    echo Mage::helper('sales')->__('Subtotal: %s', $subtotal) . PHP_EOL;
    echo Mage::helper('sales')->__('Shipping: %s', $shipping) . PHP_EOL;
    echo Mage::helper('sales')->__('Grand Total: %s', $total) . PHP_EOL;
    echo str_repeat('-', 30) . PHP_EOL;

    echo $order->getShippingDescription() . PHP_EOL;
    echo str_repeat('-', 30) . PHP_EOL;

    echo Mage::helper('payment')->getInfoBlock($order->getPayment())->toHtml() . PHP_EOL;
    echo str_repeat('-', 30) . PHP_EOL;

    echo Mage::getStoreConfig('general/store_information/name',    $order->getStoreId()) . PHP_EOL;
    echo Mage::getStoreConfig('general/store_information/address', $order->getStoreId()) . PHP_EOL;
    echo Mage::getStoreConfig('general/store_information/phone',   $order->getStoreId()) . PHP_EOL;
    echo Mage::getModel('core/date')->date('d/m/Y H:i:s') . PHP_EOL;

    $dir = Mage::getConfig()->getOptions()->getVarDir() . DS . 'draft';

    mkdir($dir, 0777, true);

    $file = sprintf('%s%s%s.txt', $dir, DS, $order->getIncrementId());

    file_put_contents($file, wordwrap(strip_tags(ob_get_contents()), 30));

    ob_end_clean();
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;

    exit(1);
}

