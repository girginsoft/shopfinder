<?php
/**
 * Created by PhpStorm.
 * User: girginsoft
 * Date: 15.11.2016
 * Time: 13:55
 */

namespace Girginsoft\Shopfinder\Model\ResourceModel\Shop\Relation\Store;


use Girginsoft\Shopfinder\Model\ResourceModel\Shop;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Girginsoft\Shopfinder\Api\Data\ShopInterface;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
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
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(ShopInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldStores = $this->resourceShop->lookupStoreIds((int)$entity->getId());
        $newStores = (array)$entity->getStores();
        if (empty($newStores)) {
            $newStores = (array)$entity->getStoreId();
        }

        $table = $this->resourceShop->getTable('shopfinder_shops_store');

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'store_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    $linkField => (int)$entity->getData($linkField),
                    'store_id' => (int)$storeId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
