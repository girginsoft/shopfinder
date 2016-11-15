<?php
/**
 * Created by PhpStorm.
 * User: girginsoft
 * Date: 15.11.2016
 * Time: 18:49
 */

namespace Girginsoft\Shopfinder\Api\Data;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for shop search results.
 * @api
 */
interface ShopSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return \Girginsoft\Shopfinder\Api\Data\ShopInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \Girginsoft\Shopfinder\Api\Data\ShopInterface[]$items
     * @return $this
     */
    public function setItems(array $items);
}
