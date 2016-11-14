<?php
/**
 * Copyright © 2015 Girginsoft. All rights reserved.
 */
namespace Girginsoft\Shopfinder\Model\ResourceModel;

/**
 * Shop resource
 */
class Shop extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('shopfinder_shops', 'shop_id');
    }

  
}
