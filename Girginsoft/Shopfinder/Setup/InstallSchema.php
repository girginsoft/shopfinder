<?php
/**
 * Created by PhpStorm.
 * User: girginsoft
 * Date: 14.11.2016
 * Time: 14:42
 */

namespace Girginsoft\Shopfinder\Setup;

use Magento\Framework\Db\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

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
                'store_view',
                Table::TYPE_TEXT,
                10,
                ['nullable' => false],
                'Store View'
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
        $installer->endSetup();
    }
}