<?php
/**
 * Created by PhpStorm.
 * User: girginsoft
 * Date: 15.11.2016
 * Time: 18:25
 */

namespace Girginsoft\Shopfinder\Api;
use Magento\Framework\Api\SearchCriteriaInterface;


interface ShopRepositoryInterface
{
    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Girginsoft\Shopfinder\Api\Data\ShopSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null);
}