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
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ManageTitles implements ModifierInterface
{
    public const FIELDSET_NAME = 'manage_titles_fieldset';

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        StoreRepositoryInterface $storeRepository,
        ArrayManager $arrayManager,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->storeRepository = $storeRepository;
        $this->arrayManager = $arrayManager;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        $attribute = $this->attributeRepository->get($data[AttributeInterface::ATTRIBUTE_CODE]);
        $data[AttributeInterface::FRONTEND_LABEL] = [
            $attribute->getDefaultFrontendLabel()
        ];
        foreach ($attribute->getFrontendLabels() as $frontendLabel) {
            $data[AttributeInterface::FRONTEND_LABEL][$frontendLabel->getStoreId()] =  $frontendLabel->getLabel();
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $labelConfigs = [];

        foreach ($this->storeRepository->getList() as $store) {
            $storeId = $store->getId();

            if (!$storeId) {
                continue;
            }
            $labelName = sprintf('%s[%d]', AttributeInterface::FRONTEND_LABEL, $storeId);
            $labelDataScope = sprintf('%s.%d', AttributeInterface::FRONTEND_LABEL, $storeId);
            $labelConfigs[$labelName] = $this->arrayManager->set(
                'arguments/data/config',
                [],
                [
                    'formElement' => Input::NAME,
                    'componentType' => Field::NAME,
                    'label' => $store->getName(),
                    'dataType' => Text::NAME,
                    'dataScope' => $labelDataScope
                ]
            );
        }
        $meta[self::FIELDSET_NAME]['children'] = $labelConfigs;

        return $meta;
    }
}
