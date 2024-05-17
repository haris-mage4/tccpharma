<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Test\Unit\Ui\DataProvider\Form\Modifier;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributesManagement\Model\Attribute\GetOptions;
use Amasty\QuoteAttributesManagement\Model\Source\FrontendInput;
use Amasty\QuoteAttributesManagement\Ui\DataProvider\Form\Modifier\OptionsContainer;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Form\Field;
use PHPUnit\Framework\TestCase;

class OptionsContainerTest extends TestCase
{
    /**
     * @covers OptionsContainer::modifyData
     *
     * @dataProvider modifyDataDataProvider
     *
     * @param string $fieldsetName
     * @param string $componentName
     * @param string $inputType
     * @param array $attributeData
     * @param array $optionsData
     * @param array $expectedResult
     */
    public function testModifyData(
        string $fieldsetName,
        string $componentName,
        string $inputType,
        array $attributeData,
        array $optionsData,
        array $expectedResult
    ): void {
        $storeRepositoryMock = $this->createMock(StoreRepositoryInterface::class);
        $arrayManagerMock = $this->createMock(ArrayManager::class);
        $attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        $getOptionsMock = $this->createMock(GetOptions::class);

        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn(
            $attributeData[AttributeInterface::FRONTEND_INPUT]
        );

        $getOptionsMock->expects($this->any())->method('execute')->willReturn($optionsData);

        $attributeRepositoryMock->expects($this->any())->method('get')->willReturn($attributeMock);

        $model = new OptionsContainer(
            $storeRepositoryMock,
            $arrayManagerMock,
            $attributeRepositoryMock,
            $getOptionsMock,
            $fieldsetName,
            $componentName,
            $inputType
        );

        $actualResult = $model->modifyData([
            AttributeInterface::ATTRIBUTE_CODE => $attributeData[AttributeInterface::ATTRIBUTE_CODE]
        ]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers OptionsContainer::modifyMeta
     *
     * @dataProvider modifyMetaDataProvider
     *
     * @param string $fieldsetName
     * @param string $componentName
     * @param string $inputType
     * @param array $storesData
     * @param array $expectedResult
     */
    public function testModifyMeta(
        string $fieldsetName,
        string $componentName,
        string $inputType,
        array $storesData,
        array $expectedResult
    ): void {
        $storeRepositoryMock = $this->createMock(StoreRepositoryInterface::class);
        $arrayManagerMock = $this->createMock(ArrayManager::class);
        $attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        $getOptionsMock = $this->createMock(GetOptions::class);

        $storeMocks = [];
        foreach ($storesData as $storeData) {
            $storeMock = $this->createMock(StoreInterface::class);
            $storeMock->expects($this->any())->method('getId')->willReturn($storeData['id']);
            $storeMock->expects($this->any())->method('getName')->willReturn($storeData['name']);
            $storeMock->expects($this->any())->method('getCode')->willReturn($storeData['code']);
            $storeMocks[] = $storeMock;
        }
        $storeRepositoryMock->expects($this->any())->method('getList')->willReturn($storeMocks);

        $arrayManagerMock->expects($this->any())->method('set')->willReturnCallback(function (
            string $path,
            array $data,
            $value
        ) {
            $data[$path] = $value;
            return $data;
        });

        $model = new OptionsContainer(
            $storeRepositoryMock,
            $arrayManagerMock,
            $attributeRepositoryMock,
            $getOptionsMock,
            $fieldsetName,
            $componentName,
            $inputType
        );

        $actualResult = $model->modifyMeta([]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyDataDataProvider(): array
    {
        return [
            [
                'attribute_options_select_container',
                'attribute_options_select',
                FrontendInput::SELECT,
                [
                    AttributeInterface::ATTRIBUTE_CODE => 'test',
                    AttributeInterface::FRONTEND_INPUT => FrontendInput::MULTISELECT
                ],
                [],
                [
                    AttributeInterface::ATTRIBUTE_CODE => 'test'
                ]
            ],
            [
                'attribute_options_multiselect_container',
                'attribute_options_multiselect',
                FrontendInput::MULTISELECT,
                [
                    AttributeInterface::ATTRIBUTE_CODE => 'test',
                    AttributeInterface::FRONTEND_INPUT => FrontendInput::MULTISELECT
                ],
                [
                    [
                        'id' => 11,
                        'sort_order' => 1,
                        'labels' => [
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        'default' => false
                    ],
                    [
                        'id' => 22,
                        'sort_order' => 2,
                        'labels' => [
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        'default' => true
                    ],
                    [
                        'id' => 33,
                        'sort_order' => 3,
                        'labels' => [
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        'default' => true
                    ]
                ],
                [
                    AttributeInterface::ATTRIBUTE_CODE => 'test',
                    'attribute_options_multiselect' => [
                        [
                            'option_id' => 11,
                            'position' => 1,
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        [
                            'option_id' => 22,
                            'position' => 2,
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        [
                            'option_id' => 33,
                            'position' => 3,
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ]
                    ],
                    'attribute_options_multiselect_default' => [
                        'option_1',
                        'option_2'
                    ]
                ]
            ],
            [
                'attribute_options_select_container',
                'attribute_options_select',
                FrontendInput::SELECT,
                [
                    AttributeInterface::ATTRIBUTE_CODE => 'test',
                    AttributeInterface::FRONTEND_INPUT => FrontendInput::SELECT
                ],
                [
                    [
                        'id' => 11,
                        'sort_order' => 1,
                        'labels' => [
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        'default' => false
                    ],
                    [
                        'id' => 22,
                        'sort_order' => 2,
                        'labels' => [
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        'default' => false
                    ],
                    [
                        'id' => 33,
                        'sort_order' => 3,
                        'labels' => [
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        'default' => true
                    ]
                ],
                [
                    AttributeInterface::ATTRIBUTE_CODE => 'test',
                    'attribute_options_select' => [
                        [
                            'option_id' => 11,
                            'position' => 1,
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        [
                            'option_id' => 22,
                            'position' => 2,
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ],
                        [
                            'option_id' => 33,
                            'position' => 3,
                            'value_option_0' => 'test',
                            'value_option_1' => 'test1'
                        ]
                    ],
                    'attribute_options_select_default' => 'option_2'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function modifyMetaDataProvider(): array
    {
        return [
            [
                'container',
                'select',
                FrontendInput::SELECT,
                [
                    [
                        'id' => 0,
                        'name' => 'admin',
                        'code' => Store::ADMIN_CODE
                    ],
                    [
                        'id' => 1,
                        'name' => 'test',
                        'code' => 'test'
                    ]
                ],
                [
                    'container/children/select/children/record/children/value_option_0/arguments/data/config' => [
                        'dataType' => 'text',
                        'formElement' => 'input',
                        'component' => 'Magento_Catalog/js/form/element/input',
                        'template' => 'Magento_Catalog/form/element/input',
                        'prefixName' => 'option.value',
                        'prefixElementName' => 'option_',
                        'suffixName' => '0',
                        'label' => 'admin',
                        'sortOrder' => 1,
                        'componentType' => Field::NAME,
                        'validation' => [
                            'required-entry' => true
                        ]
                    ],
                    'container/children/select/children/record/children/value_option_1/arguments/data/config' => [
                        'dataType' => 'text',
                        'formElement' => 'input',
                        'component' => 'Magento_Catalog/js/form/element/input',
                        'template' => 'Magento_Catalog/form/element/input',
                        'prefixName' => 'option.value',
                        'prefixElementName' => 'option_',
                        'suffixName' => '1',
                        'label' => 'test',
                        'sortOrder' => 2,
                        'componentType' => Field::NAME
                    ],
                    'container/children/select/children/record/children/action_delete/arguments/data/config' => [
                        'componentType' => 'actionDelete',
                        'dataType' => 'text',
                        'fit' => true,
                        'sortOrder' => 3,
                        'component' => 'Amasty_QuoteAttributesManagement/js/components/dynamic-rows/action-delete',
                        'template' => 'Amasty_QuoteAttributesManagement/form/element/action-delete',
                        'prefixName' => 'option.delete',
                        'prefixElementName' => 'option_'
                    ]
                ]
            ]
        ];
    }
}
