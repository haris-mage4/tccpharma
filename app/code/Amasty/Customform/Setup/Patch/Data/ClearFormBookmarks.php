<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ClearFormBookmarks implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies(): array
    {
        return [
            InstallExamples::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): ClearFormBookmarks
    {
        $connection = $this->moduleDataSetup->getConnection();

        $connection->delete(
            $this->moduleDataSetup->getTable('ui_bookmark'),
            $connection->prepareSqlCondition('namespace', ['eq' => 'amasty_customform_forms_listing'])
        );

        return $this;
    }
}
