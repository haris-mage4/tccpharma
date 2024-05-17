<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Ui\DataProvider\Form\Modifier;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class DefaultValue implements ModifierInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        /** @var AttributeInterface|Attribute $attribute */
        $attribute = $this->attributeRepository->get($data[AttributeInterface::ATTRIBUTE_CODE]);
        $defaultValueField = $attribute->getDefaultValueByInput($attribute->getFrontendInput());
        $data[$defaultValueField] = $data[AttributeInterface::DEFAULT_VALUE] ?? '';
        unset($data[AttributeInterface::DEFAULT_VALUE]);

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
