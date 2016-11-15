<?php
/**
 * Created by PhpStorm.
 * User: girginsoft
 * Date: 14.11.2016
 * Time: 14:42
 */

namespace Girginsoft\Shopfinder\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Db\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Girginsoft\Shopfinder\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('shopfinder_shops'))
            ->addColumn(
                'shop_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Id'
            )->addColumn(
                'shop_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Shop Name'
            )->addColumn(
                'identifier',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Shop Identifier'
            )->addColumn(
                'country',
                Table::TYPE_TEXT,
                25,
                ['nullable' => false],
                'Country'
            )->addColumn(
                'image',
                Table::TYPE_TEXT,
                500,
                ['nullable' => true],
                'Image'
            )->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Shop Created At'
            )->addIndex(
                $installer->getIdxName(
                    'shop_identified_idx',
                    [
                        'identifier',
                    ],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [
                    'identifier'
                ],
                [
                    'type' => AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            )->setComment(
                'Shopfinder shop table'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('shopfinder_shops_store')
            )->addColumn(
                'shop_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true, 'primary' => true],
                'Shop ID'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )->addIndex(
                $installer->getIdxName('shopfinder_shops_store', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName('sf_s', 'shop_id', 'sf_shs', 'shop_id'),
                'shop_id',
                $installer->getTable('shopfinder_shops'),
                'shop_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('sf_st', 'store_id', 'st', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
               Table::ACTION_CASCADE
            )->setComment(
                'Shop Store Linkage Table'
        );

        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}