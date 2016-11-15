<?php
/**
 * Copyright © 2015 Girginsoft. All rights reserved.
 */

namespace Girginsoft\Shopfinder\Model;

use Girginsoft\Shopfinder\Api\Data\ShopInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\ShopException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Shoptab shop model
 * @method array storeReaderreId()
 */
class Shop extends AbstractModel implements ShopInterface, IdentityInterface
{
    const SHOP_IMAGE_FOLDER = "shops/images/";
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Stripe JoinModel cache tag.
     */
    const CACHE_TAG = 'shopfinder_shops';

    /**
     * @var string
     */
    protected $_cacheTag = 'shopfinder_shops';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'shopfinder_shops';

    /**
     * @var bool
     */
    protected $_dataSaveAllowed = false;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Girginsoft\Shopfinder\Model\ResourceModel\Shop');
    }

    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteReasons();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route JoinModel.
     *
     * @return \Webkul\Stripe\Model\JoinModel
     */
    public function noRouteReasons()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Girginsoft\Shopfinder\Api\Data\ShopInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Receive page store ids
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : (array)$this->getData('store_id');
    }

    public function getIdentifier()
    {
        return $this->getData('identifier');
    }

    public function setIdentifier(string $string)
    {
        $this->setData("identifier", $string);
    }

    public function getShopName()
    {
        return $this->getData('shop_name');
    }

    public function setShopName(string $shopName)
    {
        $this->setData("shop_name", $shopName);
    }

    public function getCountry()
    {
        return $this->getData('country');
    }

    public function setCountry(string $country)
    {
        $this->setData("country", $country);
    }

    public function getImage()
    {
        if ($image = $this->getData('image')) {
            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$image;
        }

        return null;
    }

    public function setImage(string $image = null)
    {
        if ($image) {
            $this->setData("image", self::SHOP_IMAGE_FOLDER.$image);
        } else {
            $this->setData('image', null);
        }

    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save()
    {
        $this->beforeSave();
        return parent::save();
    }
    /**
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getData("identifier")) {
            $this->setData("identifier", uniqid());
        }
        return parent::beforeSave();
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Girginsoft\Shopfinder\Api\Data\ShopInterface
     */
    public function setShopId($id)
    {
        return $this->setData('shop_id', $id);
    }

    /**
     * Get ID
     *
     * @return int[]
     */
    public function getStoreId()
    {
        $this->getData('store_id');
    }

    /**
     * Get ID
     * @param int[] $ids
     * @return \Girginsoft\Shopfinder\Api\Data\ShopInterface
     */
    public function setStoreId(array $ids)
    {
        $this->setData('store_id', $ids);
        $storeIds = (array) $this->getData('store_id');
        $stores = [];
        foreach ($storeIds as $id) {
            $stores[] = $this->_storeManager->getStore($id);
        }
        $this->setData('stores', $stores);
    }
}