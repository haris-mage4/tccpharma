<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Model\Backend\Attribute;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributesManagement\Model\Backend\Attribute\Initialization\CastFrontendClassToValidationRule;
use Amasty\QuoteAttributesManagement\Model\Source\FrontendInput as FrontendInputSource;
use Amasty\QuoteAttributesManagement\Ui\DataProvider\Form\Modifier\Stores;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\Store;

class Initialization
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var FrontendInputSource
     */
    private $frontendInputSource;
    /**
     * @var CastFrontendClassToValidationRule
     */
    private $castFrontendClassToValidationRule;
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;
    /**
     * @var StringUtils
     */
    private $stringUtils;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FrontendInputSource $frontendInputSource,
        CastFrontendClassToValidationRule $castFrontendClassToValidationRule,
        JsonSerializer $jsonSerializer,
        StringUtils $stringUtils
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->frontendInputSource = $frontendInputSource;
        $this->castFrontendClassToValidationRule = $castFrontendClassToValidationRule;
        $this->jsonSerializer = $jsonSerializer;
        $this->stringUtils = $stringUtils;
    }

    /**
     * Create attribute object based on input data.
     * Used for user defined attributes.
     *
     * @param array $inputAttributeData
     * @return AttributeInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function execute(array $inputAttributeData): AttributeInterface
    {
        $attributeId = $inputAttributeData[AttributeInterface::ATTRIBUTE_ID] ?? null;
        if ($attributeId) {
            $attribute = $this->attributeRepository->getById((int) $attributeId);
            if (!$attribute->getIsUserDefined()) {
                throw new InputException(__('Could not modify system attribute.'));
            }
        } else {
            $attribute = $this->attributeRepository->getNew();
            $attribute->setIsUserDefined(true);
        }

        $attributeData = $this->retrieveAttributeData($inputAttributeData, $attribute);

        if ($attribute->getAttributeId()
            && $attribute->getFrontendInput() !== $attributeData[AttributeInterface::FRONTEND_INPUT]
        ) {
            throw new InputException(__('Could not change %1', __('Input Type')));
        }
        $attribute->addData($attributeData);

        $attribute->getExtensionAttributes()->setAmastyStores($inputAttributeData[Stores::FIELD_DATA_SCOPE] ?? []);

        return $attribute;
    }

    /**
     * Validate & filter input data.
     *
     * @param array $inputData
     * @param AttributeInterface|Attribute $attribute Used for resolve values depend on frontend_input.
     * @return array
     * @throws InputException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function retrieveAttributeData(array $inputData, AttributeInterface $attribute): array
    {
        $attributeData = [];

        if (isset($inputData[AttributeInterface::FRONTEND_LABEL][Store::DEFAULT_STORE_ID])) {
            $attributeData[AttributeInterface::FRONTEND_LABEL] = $inputData[AttributeInterface::FRONTEND_LABEL];
        } else {
            throw InputException::requiredField(__('Attribute Label'));
        }

        if (isset($inputData[AttributeInterface::ATTRIBUTE_CODE])) {
            $attributeData[AttributeInterface::ATTRIBUTE_CODE] = $inputData[AttributeInterface::ATTRIBUTE_CODE];
        } else {
            throw InputException::requiredField(__('Attribute Code'));
        }

        if (isset($inputData[AttributeInterface::FRONTEND_INPUT])) {
            if (in_array(
                $inputData[AttributeInterface::FRONTEND_INPUT],
                $this->frontendInputSource->getAvailableOptions()
            )) {
                $frontendInput = $inputData[AttributeInterface::FRONTEND_INPUT];
                $attributeData[AttributeInterface::FRONTEND_INPUT] = $frontendInput;
            } else {
                throw InputException::invalidFieldValue(
                    __('Input Type'),
                    $inputData[AttributeInterface::FRONTEND_INPUT]
                );
            }
        } else {
            throw InputException::requiredField(__('Input Type'));
        }

        $customDataModel = sprintf(
            'Amasty\QuoteAttributes\Model\Attribute\Data\%s',
            $this->stringUtils->upperCaseWords($frontendInput)
        );
        if (class_exists($customDataModel)) {
            $attributeData[AttributeInterface::DATA_MODEL] = $customDataModel;
        }

        $defaultValueField = $attribute->getDefaultValueByInput($frontendInput);
        if (isset($inputData[$defaultValueField])) {
            $attributeData[AttributeInterface::DEFAULT_VALUE] = !empty($inputData[$defaultValueField])
                ? $inputData[$defaultValueField]
                : null;
        }
        $attributeData[AttributeInterface::BACKEND_TYPE] = $attribute->getBackendTypeByInput($frontendInput);
        $attributeData[AttributeInterface::IS_REQUIRED] = $inputData[AttributeInterface::IS_REQUIRED] ?? false;
        $attributeData[AttributeInterface::FRONTEND_CLASS] = $inputData[AttributeInterface::FRONTEND_CLASS] ?? '';
        $attributeData[AttributeInterface::INPUT_FILTER] = $inputData[AttributeInterface::INPUT_FILTER] ?? '';
        $attributeData[AttributeInterface::IS_USED_IN_GRID] = $inputData[AttributeInterface::IS_USED_IN_GRID] ?? false;
        $attributeData[AttributeInterface::SORT_ORDER] = $inputData[AttributeInterface::SORT_ORDER] ?? 0;
        $attributeData[AttributeInterface::IS_FILTERABLE_IN_GRID] =
            $inputData[AttributeInterface::IS_FILTERABLE_IN_GRID] ?? false;

        if (isset($inputData['option'])) {
            $attributeData = $this->convertOptionData($inputData, $attributeData);
        }

        $validateRules = [];
        $inputValidation = $this->castFrontendClassToValidationRule->execute(
            $attributeData[AttributeInterface::FRONTEND_CLASS]
        );
        if ($inputValidation) {
            $validateRules['input_validation'] = $inputValidation;
        }
        $attributeData[AttributeInterface::VALIDATE_RULES] = $this->jsonSerializer->serialize($validateRules);

        return $attributeData;
    }

    /**
     * Retrieve options data from $inputData,
     * convert to needed format for correct save attribute,
     * put to $attributeData array.
     *
     * @param array $inputData
     * @param array $attributeData
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function convertOptionData(array $inputData, array $attributeData): array
    {
        $attributeData['option'] = $inputData['option'];
        foreach ($attributeData['option'] as $key => $value) {
            if ($key === 'delete' && is_array($value)) {
                foreach ($value as $optionId => $isDelete) {
                    if ($isDelete && !isset($attributeData['option']['value'][$optionId])) {
                        $attributeData['option']['value'][$optionId] = [];
                    }
                }
            }
        }
        if ($attributeData[AttributeInterface::FRONTEND_INPUT] === FrontendInputSource::SELECT) {
            $attributeData['default'] = isset($inputData['attribute_options_select_default'])
                ? [$inputData['attribute_options_select_default']]
                : [];
            $optionsPath = 'attribute_options_select';
        } else {
            $attributeData['default'] = $inputData['attribute_options_multiselect_default'] ?? [];
            $optionsPath = 'attribute_options_multiselect';
        }
        $fullOptionsData = $inputData[$optionsPath] ?? [];
        foreach ($fullOptionsData as $optionData) {
            if ($optionId = $optionData['option_id']) {
                $recordId = 'option_' . $optionData['record_id'];
                if (isset($attributeData['option']['value'][$recordId])) {
                    $attributeData['option']['value'][$optionId]
                        = $attributeData['option']['value'][$recordId];
                    unset($attributeData['option']['value'][$recordId]);
                }
                if (isset($attributeData['option']['order'][$recordId])) {
                    $attributeData['option']['order'][$optionId]
                        = $attributeData['option']['order'][$recordId];
                    unset($attributeData['option']['order'][$recordId]);
                }
                if (in_array($recordId, $attributeData['default'])) {
                    $attributeData['default'][] = $optionId;
                }
            }
        }

        return $attributeData;
    }
}
