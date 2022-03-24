<?php
/**
 * @package     Gamuza_MercadoLivre
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Gamuza_MercadoLivre_Model_Cron_Product extends Gamuza_MercadoLivre_Model_Cron_Abstract
{
    const PRODUCTS_GET_METHOD = 'items';
    const PRODUCTS_DESCRIPTIONS_PUT_METHOD = 'items/{productId}/descriptions/{descriptionId}';

    const DEFAULT_CURRENCY_CODE = 'BRL';
    const DEFAULT_QUEUE_LIMIT   = 60;

    protected $_apiUrl = null;

    protected $_entityTypeId = null;
    protected $_idAttribute  = null;

    public function _construct ()
    {
        $this->_apiUrl = $this->getStoreConfig ('api_url');

        $this->_entityTypeId = Mage::getModel ('eav/entity')->setType (Mage_Catalog_Model_Product::ENTITY)->getTypeId ();
        $this->_idAttribute  = Mage::getModel ('eav/entity_attribute')->loadByCode ($this->_entityTypeId, Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_ID);
    }

    private function readMercadoLivreProductsMagento ()
    {
        $collection = Mage::getModel ('catalog/product')->getCollection ()
            ->addAttributeToFilter ('type_id', array ('in' => array (
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            )))
            ->addAttributeToFilter (Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_CATEGORY, array ('notnull' => true))
            ->addAttributeToSelect (Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_ID)
        ;

        $collection->getSelect ()
            // queue
            ->joinLeft(
                array ('mercadolivre' => Gamuza_MercadoLivre_Helper_Data::PRODUCT_TABLE),
                'e.entity_id = mercadolivre.product_id',
                array (
                    'mercadolivre_updated_at' => 'mercadolivre.updated_at',
                    'mercadolivre_synced_at'  => 'mercadolivre.synced_at'
                )
            )
            ->where ('mercadolivre.synced_at IS NULL OR mercadolivre.synced_at < e.updated_at')
            // orphans
            ->joinLeft (
                array ('relation' => 'catalog_product_relation'),
                'e.entity_id = relation.child_id',
                array ('parent_id')
            )
            ->where (sprintf ("(type_id = '%s' AND parent_id IS NULL) || (type_id = '%s')",
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            ))
        ;

        $sellerId = $this->getStoreConfig ('user_id');

        foreach ($collection as $product)
        {
            $externalId = $product->getData (Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_ID);
            $category   = json_decode ($product->getData (Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_CATEGORY));

            $mercadolivreProduct = Mage::getModel ('mercadolivre/product')->load ($product->getId (), 'product_id')
                ->setProductId ($product->getId ())
                ->setExternalId ($externalId)
                ->setSellerId ($sellerId)
                ->setCategoryId ($category [0])
                ->setCategoryName ($category [1])
                ->setStatus (Gamuza_MercadoLivre_Helper_Data::QUEUE_STATUS_PENDING)
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;
        }

        return true;
    }

    private function readMercadoLivreProductsCollection ()
    {
        $limit = intval (Mage::getStoreConfig ('mercadolivre/queue/product'));

        $collection = Mage::getModel ('mercadolivre/product')->getCollection ();

        $collection->getSelect ()
            ->where ('synced_at IS NULL OR synced_at < updated_at')
            ->order ('updated_at DESC')
            ->order ('status DESC')
            ->limit ($limit ? $limit : self::DEFAULT_QUEUE_LIMIT)
        ;

        return $collection;
    }

    private function updateProducts ($collection)
    {
        foreach ($collection as $product)
        {
            $result = null;

            try
            {
                $result = $this->updateMercadoLivreProduct ($product);
            }
            catch (Exception $e)
            {
                $this->logMercadoLivreProduct ($product, $e->getMessage ());
            }

            if (!empty ($result)) $this->cleanupMercadoLivreProduct ($product, $result);
        }

        return true;
    }

    private function updateMercadoLivreProduct (Gamuza_MercadoLivre_Model_Product $product)
    {
        $productId = $product->getProductId ();

        $mageProduct = Mage::getModel ('catalog/product')->load ($productId);

        if (!$mageProduct || !$mageProduct->getId ())
        {
            return false;
        }

        $externalId = $mageProduct->getData (Gamuza_MercadoLivre_Helper_Data::PRODUCT_ATTRIBUTE_ID);

        $listingType  = Mage::getStoreConfig ('mercadolivre/product/listing_type');
        $availableQty = !strcmp ($listingType, 'free') ? 1 : floatval ($mageProduct->getStockItem ()->getQty ());

        $post = array(
            'category_id' => $product->getCategoryId (),
            'title'       => $mageProduct->getName (),
            'description' => array(
                'plain_text' => $mageProduct->getShortDescription (),
            ),
            'price'       => floatval ($mageProduct->getFinalPrice ()),
            'pictures'    => array (),
            'available_quantity' => $availableQty,
            // custom
            'currency_id'     => self::DEFAULT_CURRENCY_CODE,
            'listing_type_id' => $listingType,
            'buying_mode'     => 'buy_it_now',
            'condition'       => 'new',
            'shipping'        => array(
                'mode'          => Mage::getStoreConfig ('mercadolivre/product/shipping_mode'),
                'free_shipping' => Mage::getStoreConfigFlag ('mercadolivre/product/free_shipping'),
                'local_pick_up' => Mage::getStoreConfigFlag ('mercadolivre/product/local_pick_up'),
            ),
        );

        $mediaUrl = Mage::app ()
            ->getStore (Mage_Core_Model_App::DISTRO_STORE_ID)
            ->getBaseUrl (Mage_Core_Model_Store::URL_TYPE_MEDIA, false)
        ;

        $attribute = $mageProduct->getResource ()->getAttribute ('media_gallery');
        $attribute->getBackend ()->afterLoad ($mageProduct);

        foreach ($mageProduct->getMediaGalleryImages () as $_image)
        {
            $post ['pictures'][] = array(
                'source' => sprintf ('%s/catalog/product/%s', $mediaUrl, (string) $_image->getFile ()),
            );
        }

        $result   = null;
        $response = null;

        if (!empty ($externalId))
        {
            $productsGetMethod = sprintf ('%s/%s', self::PRODUCTS_GET_METHOD, $externalId);

            try
            {
                $response = $this->getHelper ()->api ($this->_apiUrl . '/' . $productsGetMethod);
            }
            catch (Exception $e)
            {
                if ($e->getCode () != 404)
                {
                    throw Mage::exception ('Gamuza_MercadoLivre', $e->getMessage (), $e->getCode ());
                }
            }
        }

        $accessToken = '?access_token=' . $this->getStoreConfig ('access_token');

        if ($response && strcmp ($response->status, 'closed') != 0)
        {
            try
            {
                $productsPutMethod = sprintf ('%s/%s', self::PRODUCTS_GET_METHOD, $externalId);

                unset ($post ['description']);
                unset ($post ['listing_type_id']);

                $this->getHelper ()->api ($this->_apiUrl . '/' . $productsPutMethod . $accessToken, $post, 'PUT');

                // descriptions
                foreach ($response->descriptions as $description)
                {
                    $productsDescriptionsPostMethod = str_replace (
                        array ('{productId}', '{descriptionId}'),
                        array ($externalId, $description->id),
                        self::PRODUCTS_DESCRIPTIONS_PUT_METHOD
                    );

                    $post = array (
                        'plain_text' => $mageProduct->getShortDescription ()
                    );

                    $this->getHelper ()->api ($this->_apiUrl . '/' . $productsDescriptionsPostMethod . $accessToken, $post, 'PUT');
                }

                $result = true;
            }
            catch (Exception $e)
            {
                throw Mage::exception ('Gamuza_MercadoLivre', $e->getMessage (), $e->getCode ());
            }
        }
        else
        {
            try
            {
                $response = $this->getHelper ()->api ($this->_apiUrl . '/' . self::PRODUCTS_GET_METHOD . $accessToken, $post);

                $resource = Mage::getSingleton ('core/resource');
                $write    = $resource->getConnection ('core_write');
                $table    = $resource->getTableName ('catalog_product_entity_' . $this->_idAttribute->getBackendType ());

                $write->insertOnDuplicate ($table, array (
                    'entity_type_id' => $this->_entityTypeId,
                    'attribute_id'   => $this->_idAttribute->getId (),
                    'store_id'       => Mage_Core_Model_App::ADMIN_STORE_ID,
                    'entity_id'      => $mageProduct->getId (),
                    'value'          => $response->id,
                ));

                $result = $response->id;
            }
            catch (Exception $e)
            {
                throw Mage::exception ('Gamuza_MercadoLivre', $e->getMessage (), $e->getCode ());
            }
        }

        return $result;
    }

    private function cleanupMercadoLivreProduct (Gamuza_MercadoLivre_Model_Product $product, $externalId)
    {
        if ($externalId !== null && $externalId !== true)
        {
            $product->setExternalId ($externalId);
        }

        $product->setSyncedAt (date ('c'))
            ->setStatus (Gamuza_MercadoLivre_Helper_Data::QUEUE_STATUS_OKAY)
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->save ()
        ;

        return true;
    }

    private function logMercadoLivreProduct (Gamuza_MercadoLivre_Model_Product $product, $message = null)
    {
        $product->setStatus (Gamuza_MercadoLivre_Helper_Data::QUEUE_STATUS_ERROR)->setMessage ($message)->save ();
    }

    public function run ()
    {
        if ($this->getStoreConfig ('active'))
        {
            $result = $this->readMercadoLivreProductsMagento ();

            if (!$result)
            {
                return false;
            }

            $collection = $this->readMercadoLivreProductsCollection ();

            if (!$collection->getSize ())
            {
                return false;
            }

            $this->updateProducts ($collection);
        }
    }
}

