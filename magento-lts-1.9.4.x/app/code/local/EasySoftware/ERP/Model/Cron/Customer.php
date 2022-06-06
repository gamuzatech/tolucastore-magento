<?php
/**
 * @package     EasySoftware_ERP
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class EasySoftware_ERP_Model_Cron_Customer extends EasySoftware_ERP_Model_Cron_Abstract
{
    const DEFAULT_QUEUE_LIMIT = 60;

    const CUSTOMER_TYPE_NORMAL = 'FÍSICA';
    const CUSTOMER_TYPE_LEGAL  = 'JURÍDICA';

    const CUSTOMER_GENDER_FEMALE = 'FEMININO';
    const CUSTOMER_GENDER_MALE   = 'MASCULINO';

    const CUSTOMER_CPF_PATTERN     = '/(\d{3})(\d{3})(\d{3})(\d{2})/';
    const CUSTOMER_CPF_REPLACEMENT = '$1.$2.$3-$4';

    const CUSTOMER_CNPJ_PATTERN     = '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/';
    const CUSTOMER_CNPJ_REPLACEMENT = '$1.$2.$3/$4-$5';

    private function readERPCustomersMagento ()
    {
        $companyId = $this->getStoreConfig ('company_id');

        $limit = self::DEFAULT_QUEUE_LIMIT;

        $collection = Mage::getModel ('customer/customer')->getCollection ()
            ->addFieldToFilter ('taxvat', array ('notnull' => true))
            ->addNameToSelect ()
        ;

        $collection->getSelect ()
            ->joinLeft(
                array ('erp' => EasySoftware_ERP_Helper_Data::CUSTOMER_TABLE),
                'e.entity_id = erp.customer_id',
                array ()
            )
            ->where ('e.updated_at > erp.synced_at OR erp.synced_at IS NULL')
        ;

        foreach ($collection as $mageCustomer)
        {
            $customer = Mage::getModel ('erp/customer')->load ($mageCustomer->getId (), 'customer_id');

            $customerTaxvat = preg_replace ('[\D]', '', $mageCustomer->getTaxvat ());

            $customer->setCustomerId ($mageCustomer->getId ())
                ->setCustomerEmail ($mageCustomer->getEmail ())
                ->setCustomerName ($mageCustomer->getName ())
                ->setCustomerTaxvat ($customerTaxvat)
                ->setCompanyId ($companyId)
                ->setIsActive ($mageCustomer->getIsActive ())
                ->setStatus (EasySoftware_ERP_Helper_Data::STATUS_PENDING)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }
    }

    private function readERPCustomersCollection ()
    {
        $limit = self::DEFAULT_QUEUE_LIMIT;

        $collection = Mage::getModel ('erp/customer')->getCollection ()
            ->addFieldToFilter ('status', array ('neq' => EasySoftware_ERP_Helper_Data::STATUS_OKAY))
        ;

        $collection->getSelect ()
            ->where ('synced_at IS NULL OR synced_at < updated_at')
            ->limit ($limit ?? self::DEFAULT_QUEUE_LIMIT)
        ;

        return $collection;
    }

    private function updateERPCustomersAPI ($collection)
    {
        foreach ($collection as $customer)
        {
            $result = null;

            try
            {
                $result = $this->updateERPCustomer ($customer);
            }
            catch (Exception $e)
            {
                $this->logERPCustomer ($customer, $e->getMessage ());

                self::logException ($e);
            }

            if (!empty ($result)) $this->cleanupERPCustomer ($customer);
        }

        return true;
    }

    private function updateERPCustomer ($customer)
    {
        $companyId  = $customer->getCompanyId ();
        $externalId = $customer->getExternalId ();

        $customerTaxvat = strlen ($customer->getCustomerTaxvat ()) == 14
            ? preg_replace (self::CUSTOMER_CNPJ_PATTERN, self::CUSTOMER_CNPJ_REPLACEMENT, $customer->getCustomerTaxvat ())
            : preg_replace (self::CUSTOMER_CPF_PATTERN, self::CUSTOMER_CPF_REPLACEMENT, $customer->getCustomerTaxvat ())
        ;

$queryTaxvat = <<< QUERY
    SELECT CODIGO FROM PESSOA
    WHERE EMPRESA = {$companyId}
    AND CNPJ = '{$customerTaxvat}'
QUERY;

        if (empty ($externalId))
        {
            $result = Mage::helper ('erp')->query ($queryTaxvat);

            $row = ibase_fetch_object ($result);

            $externalId = is_object ($row) && intval ($row->CODIGO) > 0 ? $row->CODIGO : 0;

            if (!$externalId)
            {

$queryMax = <<< QUERY
    SELECT MAX(CODIGO) FROM PESSOA
    WHERE EMPRESA = {$companyId}
QUERY;

                $result = Mage::helper ('erp')->query ($queryMax);

                $row = ibase_fetch_object ($result);

                $externalId = is_object ($row) && intval ($row->MAX) > 0 ? $row->MAX + 1: 1;
            }
        }

        $customerId = $customer->getCustomerId ();
        $customerEmail = $customer->getCustomerEmail ();

        $customerActive = $customer->getIsActive () ? 'S' : 'N';
        $customerName = utf8_decode ($customer->getCustomerName ());

        $customerType = utf8_decode (strlen ($customer->getCustomerTaxvat ()) == 14
            ? self::CUSTOMER_TYPE_LEGAL : self::CUSTOMER_TYPE_NORMAL
        );

        $mageCustomer = Mage::getModel ('customer/customer')->load ($customer->getCustomerId ());

        if (!$mageCustomer || !$mageCustomer->getId ())
        {
            throw new Exception (Mage::helper ('erp')->__('Customer was not found.'));
        }

        $customerDob = substr ($mageCustomer->getDob (), 0, 10);

        $customerGender = $mageCustomer->getGender () == '1'
            ? self::CUSTOMER_GENDER_MALE : self::CUSTOMER_GENDER_FEMALE
        ;

        $billingAddress = $mageCustomer->getDefaultBillingAddress ();

        $billingCompany    = utf8_decode ($billingAddress->getCompany ());
        $billingStreet     = utf8_decode ($billingAddress->getStreet1 ());
        $billingNumber     = utf8_decode ($billingAddress->getStreet2 ());
        $billingComplement = utf8_decode ($billingAddress->getStreet3 ());
        $billingDistrict   = utf8_decode ($billingAddress->getStreet4 ());
        $billingCity       = utf8_decode ($billingAddress->getCity ());
        $billingRegion     = $billingAddress->getRegionCode ();
        $billingZipcode    = preg_replace ('[\D]', '', $billingAddress->getPostcode ());
        $billingTelephone  = preg_replace ('[\D]', '', $billingAddress->getTelephone ());
        $billingCellphone  = preg_replace ('[\D]', '', $billingAddress->getFax ());

        $billingTown = preg_replace ('/[^a-zA-Z\s]/', '%', strtoupper ($billingAddress->getCity ()));

$queryCity = <<< QUERY
    SELECT CODIGO FROM CIDADE
    WHERE DESCRICAO LIKE '{$billingTown}'
    AND UF = '{$billingRegion}'
QUERY;

$queryCustomer = <<< QUERY
    UPDATE OR INSERT INTO PESSOA(
        EMPRESA,
        CODIGO,
        TIPO,
        CNPJ,
        IE,
        FANTASIA,
        ENDERECO,
        NUMERO,
        COMPLEMENTO,
        BAIRRO,
        CODMUN,
        MUNICIPIO,
        UF,
        CEP,
        FONE1,
        CELULAR1,
        EMAIL1,
        SEXO,
        DT_NASC,
        ISENTO,
        FORN,
        FUN,
        CLI,
        FAB,
        TRAN,
        ADM,
        ATIVO,
        REFERENCIA,
        REGIME_TRIBUTARIO,
        RAZAO
    )
    VALUES(
        {$companyId},
        {$externalId},
        '{$customerType}',
        '{$customerTaxvat}',
        '',
        '{$billingCompany}',
        '{$billingStreet}',
        '{$billingNumber}',
        '{$billingComplement}',
        '{$billingDistrict}',
        ({$queryCity}),
        '{$billingCity}',
        '{$billingRegion}',
        '{$billingZipcode}',
        '{$billingTelephone}',
        '{$billingCellphone}',
        '{$customerEmail}',
        '{$customerGender}',
        '{$customerDob}',
        1,
        'N',
        'N',
        'S',
        'N',
        'N',
        'N',
        '{$customerActive}',
        {$externalId},
        'SIMPLES',
        '{$customerName}'
    )
    MATCHING (CODIGO);
QUERY;

        $result = Mage::helper ('erp')->query ($queryCustomer);

        if ($result != 1)
        {
            throw new Exception (Mage::helper ('erp')->__('Unable to save customer.'));
        }

        $result = Mage::helper ('erp')->query ($queryTaxvat);

        $row = ibase_fetch_object ($result);

        $customer->setExternalId ($row->CODIGO)
            ->setExternalCode ($row->CODIGO)
        ;

        return $mageCustomer->getId ();
    }

    private function cleanupERPCustomer ($customer)
    {
        $customer->setSyncedAt (date ('c'))
            ->setStatus (EasySoftware_ERP_Helper_Data::STATUS_OKAY)
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->save ();
    }

    private function logERPCustomer ($customer, $message = null)
    {
        $customer->setStatus (EasySoftware_ERP_Helper_Data::STATUS_ERROR)
            ->setMessage ($message)
            ->save ();
    }

    public function run ()
    {
        if ($this->getStoreConfig ('active'))
        {
            $result = $this->readERPCustomersMagento ();

            $collection = $this->readERPCustomersCollection ();

            if ($collection->getSize ())
            {
                $this->updateERPCustomersAPI ($collection);
            }
        }
    }
}

