<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\Ordernotes\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;

class InstallSchema implements InstallSchemaInterface
{
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('um_ordernotes'))
            ->addColumn(
                'id',
                DdlTable::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true,
                    'nullable' => false, 'primary' => true
                ],
                'Id'
            )
            ->addColumn(
                'order_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['default' => null],
                'Order ID'
            )
            ->addColumn(
                'note',
                DdlTable::TYPE_TEXT,
                null,
                ['default' => null],
                'Note'
            )
             ->addColumn(
                 'customer_id',
                 DdlTable::TYPE_INTEGER,
                 null,
                 ['default' => null],
                 'Customer ID'
             )
            ->addColumn(
                'user_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['default' => null],
                'User ID'
            )
            ->addColumn(
                'created_at',
                DdlTable::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => DdlTable::TIMESTAMP_INIT],
                'Added At'
            )
           ->addColumn(
               'store_name',
               DdlTable::TYPE_TEXT,
               100,
               ['nullable' => true],
               'Store Name'
           )
            ->addColumn(
                'updated_at',
                DdlTable::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => DdlTable::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addColumn(
                'new_customer_note',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => true],
                'New customer note'
            )
            ->addColumn(
                'new_admin_note',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => true],
                'New admin note'
            )
            ->addColumn(
                'new_customer_note_markread',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => true],
                'New customer note markread'
            )
            ->addColumn(
                'new_admin_note_markread',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => true],
                'New admin note markread'
            )
            ->addColumn(
                'visible',
                DdlTable::TYPE_SMALLINT,
                null,
                ['default' => null],
                'Visible to customer'
            );
    
        $installer->getConnection()
            ->createTable($table);
            
        $installer->endSetup();
    }
}
