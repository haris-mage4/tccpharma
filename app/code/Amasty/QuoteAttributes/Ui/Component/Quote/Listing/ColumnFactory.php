<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Ui\Component\Quote\Listing;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Source\Attribute\FrontendInput;
use IntlDateFormatter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;

class ColumnFactory
{
    /**
     * @var UiComponentFactory
     */
    private $componentFactory;

    /**
     * @var array
     */
    private $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'multiselect' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date'
    ];

    /**
     * @var array
     */
    private $dataTypeMap = [
        'default' => 'text',
        FrontendInput::TEXT => 'text',
        FrontendInput::BOOLEAN => 'select',
        FrontendInput::SELECT => 'select',
        FrontendInput::MULTISELECT => 'multiselect',
        FrontendInput::DATE => 'date'
    ];

    /**
     * @var array
     */
    private $filterMap = [
        'default' => 'text',
        FrontendInput::SELECT => 'select',
        FrontendInput::BOOLEAN => 'select',
        FrontendInput::MULTISELECT => 'select',
        FrontendInput::DATE => 'dateRange'
    ];

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        UiComponentFactory $componentFactory,
        TimezoneInterface $timezone
    ) {
        $this->componentFactory = $componentFactory;
        $this->timezone = $timezone;
    }

    /**
     * @param AttributeInterface|Attribute $attribute
     * @param ContextInterface $context
     * @param array $config
     *
     * @return ColumnInterface
     * @throws LocalizedException
     */
    public function create(
        AttributeInterface $attribute,
        ContextInterface $context,
        array $config = []
    ): ColumnInterface {
        $columnName = $attribute->getAttributeCode();
        $config = array_merge(
            [
                'label' => __($attribute->getDefaultFrontendLabel()),
                'dataType' => $this->getDataType($attribute->getFrontendInput()),
                'visible' => true,
                'filter' => $this->isFilterable($attribute, $context)
                    ? $this->getFilterType($attribute->getFrontendInput())
                    : null
            ],
            $config
        );

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
            foreach ($config['options'] as &$optionData) {
                $optionData['__disableTmpl'] = true;
            }
        }

        $config['component'] = $this->getJsComponent($config['dataType']);

        if ($attribute->getFrontendInput() === FrontendInput::DATE) {
            $config += $this->getDateConfig();
        }

        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];

        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

    private function getDateConfig(): array
    {
        $dateFormat = $this->timezone->getDateFormat(IntlDateFormatter::MEDIUM);
        $timezone = $this->timezone->getDefaultTimezone();

        return [
            'timezone' => $timezone,
            'dateFormat' => $dateFormat,
            'options' => ['showsTime' => false]
        ];
    }

    private function getJsComponent(string $dataType): string
    {
        return $this->jsComponentMap[$dataType];
    }

    private function getDataType(string $frontendInput): string
    {
        return $this->dataTypeMap[$frontendInput] ?? $this->dataTypeMap['default'];
    }

    private function getFilterType(string $frontendInput): string
    {
        return $this->filterMap[$frontendInput] ?? $this->filterMap['default'];
    }

    private function isFilterable(AttributeInterface $attribute, ContextInterface $context): bool
    {
        $filterModifiers = $context->getRequestParam(FilterModifier::FILTER_MODIFIER, []);
        return $attribute->isFilterableInGrid() || array_key_exists($attribute->getAttributeCode(), $filterModifiers);
    }
}
