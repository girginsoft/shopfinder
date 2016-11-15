<?php
/**
 * Created by PhpStorm.
 * User: girginsoft
 * Date: 15.11.2016
 * Time: 18:24
 */

namespace Girginsoft\Shopfinder\Model;

use Girginsoft\Shopfinder\Api\Data;
use Girginsoft\Shopfinder\Api\ShopRepositoryInterface;
use Girginsoft\Shopfinder\Block\Adminhtml\Shop;
use Girginsoft\Shopfinder\Model\ResourceModel\Shop as ResourceShop;
use Girginsoft\Shopfinder\Model\ResourceModel\Shop\Grid\CollectionFactory as ShopCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;


class ShopRepository implements ShopRepositoryInterface
{
    /**
     * @var ResourceShop
     */
    protected $resource;

    /**
     * @var ShopFactory
     */
    protected $shopFactory;

    /**
     * @var ShopCollectionFactory
     */
    protected $shopCollectionFactory;

    /**
     * @var Data\ShopSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Girginsoft\Shopfinder\Api\Data\ShopInterfaceFactory
     */
    protected $dataShopFactory;
    /**
     * @var \Magento\Store\Api\Data\StoreInterfaceFactory
     */
    protected $dataStoreFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceShop $resource
     * @param ShopFactory $shopFactory
     * @param Data\ShopInterfaceFactory $dataShopFactory
     * @param \Magento\Store\Api\Data\StoreInterfaceFactory $dataStoreFactory
     * @param ShopCollectionFactory $shopCollectionFactory
     * @param Data\ShopSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceShop $resource,
        ShopFactory $shopFactory,
        Data\ShopInterfaceFactory $dataShopFactory,
        \Magento\Store\Api\Data\StoreInterfaceFactory $dataStoreFactory,
        ShopCollectionFactory $shopCollectionFactory,
        Data\ShopSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->shopFactory = $shopFactory;
        $this->shopCollectionFactory = $shopCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataShopFactory = $dataShopFactory;
        $this->dataStoreFactory = $dataStoreFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Girginsoft\Shopfinder\Api\Data\ShopSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {

        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->shopCollectionFactory->create();
        if ($this->storeManager->getStore()->getCode() != "default") {
            $collection->addStoreFilter($this->storeManager->getStore()->getId(), false);
        }
        if ($searchCriteria) {
            $searchResults->setSearchCriteria($searchCriteria);
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    $condition = $filter->getConditionType() ?: 'eq';
                    $field = $filter->getField();
                    if ($field == "name") {
                        $field = "shop_name";
                    }
                    $collection->addFieldToFilter($field, [$condition => $filter->getValue()]);
                }
            }
            $collection->setCurPage($searchCriteria->getCurrentPage());
            $collection->setPageSize($searchCriteria->getPageSize());
        }
        $searchResults->setTotalCount($collection->getSize());

        $shops = [];
        foreach ($collection as $shopModel) {
            $shopData = $this->dataShopFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $shopData,
                $shopModel->getData(),
                'Girginsoft\Shopfinder\Api\Data\ShopInterface'
            );
            $shopData->setStoreId($shopModel->getData('store_id'));
            $stores = [];
            foreach ($shopData->getStores() as $store) {
                $storeData = $this->dataStoreFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $storeData,
                    $store->getData(),
                    '\Magento\Store\Api\Data\StoreInterface'
                );
                $stores[] = $this->dataObjectProcessor->buildOutputDataArray(
                    $storeData,
                    '\Magento\Store\Api\Data\StoreInterface'
                );
            }
            $shop = $this->dataObjectProcessor->buildOutputDataArray(
                $shopData,
                'Girginsoft\Shopfinder\Api\Data\ShopInterface'
            );
            $shop['stores'] = $stores;
            $shops[] = $shop;

        }
        $searchResults->setItems($shops);

        return $searchResults;
    }
}