<?php

namespace Stathmos\QuoteCommunication\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('quote_remark_communication')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('quote_remark_communication')
			)
				->addColumn(
					'id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'ID'
				)
				->addColumn(
					'quote_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[],
					'Quote Id'
				)
				->addColumn(
					'customer_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[],
					'Customer Id'
				)
				->addColumn(
					'admin_user_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[],
					'Admin User Id'
				)
				->addColumn(
					'admin_user_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					null,
					[],
					'Admin User Name'
				)
				->addColumn(
					'customer_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					null,
					[],
					'Customer Name'
				)
				->addColumn(
					'remark_comment',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100000,
					[],
					'Remark Comment'
				)
				->addColumn(
					'notify_by_customer',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[],
					'Notify By Customer'
				)
				->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('Quote Remark Communication Table');
			$installer->getConnection()->createTable($table);
		}
		$installer->endSetup();
	}
}