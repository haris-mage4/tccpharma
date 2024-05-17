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
use Amasty\QuoteAttributesManagement\Model\Attribute\GetOptions;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class OptionsContainer implements ModifierInterface
{
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

    /**
     * @var string
     */
    private $fieldsetName;

    /**
     * @var string
     */
    private $componentName;

    /**
     * @var GetOptions
     */
    private $getOptions;

    /**
     * @var string
     */
    private $inputType;

    public function __construct(
        StoreRepositoryInterface $storeRepository,
        ArrayManager $arrayManager,
        AttributeRepositoryInterface $attributeRepository,
        GetOptions $getOptions,
        string $fieldsetName,
        string $componentName,
        string $inputType
    ) {
        $this->storeRepository = $storeRepository;
        $this->arrayManager = $arrayManager;
        $this->attributeRepository = $attributeRepository;
        $this->fieldsetName = $fieldsetName;
        $this->componentName = $componentName;
        $this->getOptions = $getOptions;
        $this->inputType = $inputType;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        $attribute = $this->attributeRepository->get($data[AttributeInterface::ATTRIBUTE_CODE]);

        if ($this->inputType !== $attribute->getFrontendInput()) {
            return $data;
        }

        $defaultValues = [];
        foreach ($this->getOptions->execute($attribute) as $key => $optionData) {
            $optionUiData = [
                'position' => $optionData['sort_order'],
                'option_id' => $optionData['id']
            ];

            foreach ($optionData['labels'] as $labelCode => $labelValue) {
                $optionUiData[$labelCode] = $labelValue;
            }

            $data[$this->componentName][] = $optionUiData;
            if ($optionData['default']) {
                $defaultValues[] = sprintf('option_%d', $key);
            }
        }
        if (count($defaultValues) === 1) {
            $defaultValues = array_shift($defaultValues);
        }
        $data[$this->componentName . '_default'] = $defaultValues;

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $recordPath = sprintf('%s/children/%s/children/record/children', $this->fieldsetName, $this->componentName);

        $sortOrder = 1;
        foreach ($this->getStoresList() as $store) {
            $storeId = $store->getId();
            $storeLabelConfiguration = [
                'dataType' => 'text',
                'formElement' => 'input',
                'component' => 'Magento_Catalog/js/form/element/input',
                'template' => 'Magento_Catalog/form/element/input',
                'prefixName' => 'option.value',
                'prefixElementName' => 'option_',
                'suffixName' => (string)$storeId,
                'label' => $store->getName(),
                'sortOrder' => $sortOrder++,
                'componentType' => Field::NAME,
            ];
            if ($store->getCode() === Store::ADMIN_CODE) {
                $storeLabelConfiguration['validation'] = [
                    'required-entry' => true,
                ];
            }
            $meta = $this->arrayManager->set(
                sprintf('%s/value_option_%d/arguments/data/config', $recordPath, $storeId),
                $meta,
                $storeLabelConfiguration
            );
        }

        $meta = $this->arrayManager->set(
            sprintf('%s/action_delete/arguments/data/config', $recordPath),
            $meta,
            [
                'componentType' => 'actionDelete',
                'dataType' => 'text',
                'fit' => true,
                'sortOrder' => $sortOrder,
                'component' => 'Amasty_QuoteAttributesManagement/js/components/dynamic-rows/action-delete',
                'template' => 'Amasty_QuoteAttributesManagement/form/element/action-delete',
                'prefixName' => 'option.delete',
                'prefixElementName' => 'option_'
            ]
        );

        return $meta;
    }

    private function getStoresList(): array
    {
        $storesById = [];
        foreach ($this->storeRepository->getList() as $store) {
            $storesById[$store->getId()] = $store;
        }

        ksort($storesById);

        return $storesById;
    }
}
