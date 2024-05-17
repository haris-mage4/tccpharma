<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Setup\Patch\Data;

use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\ResourceModel\Attribute as AttributeResource;
use Amasty\QuoteAttributes\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddEntityType implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $setup)
    {
        $this->eavSetup = $eavSetupFactory->create(['setup' => $setup]);
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddEntityType
     */
    public function apply()
    {
        $this->eavSetup->addEntityType(
            QuoteEntity::TYPE_CODE,
            [
                'entity_model' => QuoteEntity::class,
                'attribute_model' => Attribute::class,
                'table' => QuoteEntity::TABLE_NAME,
                'entity_attribute_collection' => AttributeCollection::class,
                'additional_attribute_table' => AttributeResource::TABLE_NAME
            ]
        );

        return $this;
    }

    /**
     * @return void
     */
    public function revert()
    {
        $this->eavSetup->removeEntityType(QuoteEntity::TYPE_CODE);
    }
}
