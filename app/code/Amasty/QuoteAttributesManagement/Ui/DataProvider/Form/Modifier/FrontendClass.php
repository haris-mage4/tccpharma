<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Ui\DataProvider\Form\Modifier;

use Amasty\QuoteAttributesManagement\Model\Source\FrontendClass as FrontendClassSource;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class FrontendClass implements ModifierInterface
{
    public const BASE_FIELDSET_NAME = 'base_fieldset';
    public const COMPONENT_NAME = 'frontend_class';

    /**
     * @var FrontendClassSource
     */
    private $frontendClassSource;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(FrontendClassSource $frontendClassSource, ArrayManager $arrayManager)
    {
        $this->frontendClassSource = $frontendClassSource;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $meta[self::BASE_FIELDSET_NAME]['children'][self::COMPONENT_NAME] = $this->arrayManager->set(
            'arguments/data/config/optionsByType',
            [],
            $this->frontendClassSource->getOptionsByType()
        );

        return $meta;
    }
}
