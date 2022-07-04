<?php
class RicardoMartins_PagSeguroPro_Block_Payment_Info extends Mage_Core_Block_Template
{
    public function getPaymentInfo()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $paymentMethod = $order->getPayment()->getMethod();

            switch($paymentMethod)
            {
                case 'pagseguropro_boleto':
                    return array(
                        'tipo' => 'Boleto',
                        'url' => $order->getPayment()->getAdditionalInformation('boletoUrl'),
                        'texto' => 'Clique aqui para imprimir seu boleto',
                    );
                    break;
                case 'pagseguropro_tef':
                    return array(
                        'tipo' => 'Débito Online (TEF)',
                        'url' => $order->getPayment()->getAdditionalInformation('tefUrl'),
                        'texto' => 'Clique aqui para realizar o pagamento',
                    );
                    break;
            }
        }
        return false;
    }
}