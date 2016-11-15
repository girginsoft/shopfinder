<?php
/**
 * Copyright © 2015 Girginsoft. All rights reserved.
 */
namespace Girginsoft\Shopfinder\Model\ResourceModel;

use Girginsoft\Shopfinder\Api\Data\ShopInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;
/**
 * Shop resource
 */
class Shop extends AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('shopfinder_shops', 'shop_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(ShopInterface::class)->getEntityConnection();
    }

    /**
     * @param AbstractModel $object
     * @param string $value
     * @param string|null $field
     * @return bool|int|string
     * @throws LocalizedException
     * @throws \Exception
     */
    private function getShopId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(ShopInterface::class);

        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }

        $shopId = $value;
        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $shopId = count($result) ? $result[0] : false;
        }
        return $shopId;
    }

    /**
     * Load an object
     *
     * @param Shop|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $shopId = $this->getShopId($object, $value, $field);
        if ($shopId) {
            $this->entityManager->load($object, $shopId);
        }
        return $this;
    }
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param CmsPage|AbstractModel $object
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(ShopInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        $select->join(
            ['shopfinder_shops_store' => $this->getTable('shopfinder_shops_store')],
            $this->getMainTable().'.'.$linkField.' = shopfinder_shops_store.'.$linkField,
            []
        )
            ->order('shopfinder_shops_store.store_id DESC')
            ->limit(1);
        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int|int $shopId
     * @return array
     */
    public function lookupStoreIds(int $shopId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(ShopInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cps' => $this->getTable('shopfinder_shops_store')], 'store_id')
            ->join(
                ['cp' => $this->getMainTable()],
                'cps.'.$linkField.' = cp.'.$linkField,
                []
            )
            ->where('cp.'.$entityMetadata->getIdentifierField().' = :shop_id');

        return $connection->fetchCol($select, ['shop_id' => (int)$shopId]);
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;

        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }

    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
