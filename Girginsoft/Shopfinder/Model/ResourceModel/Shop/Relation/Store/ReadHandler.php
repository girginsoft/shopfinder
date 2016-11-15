<?php
/**
 * Created by PhpStorm.
 * User: girginsoft
 * Date: 15.11.2016
 * Time: 13:35
 */

namespace Girginsoft\Shopfinder\Model\ResourceModel\Shop\Relation\Store;

use Girginsoft\Shopfinder\Model\ResourceModel\Shop;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Shop
     */
    protected $resourceShop;

    /**
     * @param MetadataPool $metadataPool
     * @param Shop $resourceShop
     */
    public function __construct(
        MetadataPool $metadataPool,
        Shop $resourceShop
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceShop = $resourceShop;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceShop->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
        }
        return $entity;
    }
}