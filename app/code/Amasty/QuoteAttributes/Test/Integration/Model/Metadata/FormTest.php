<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Test\Integration\Model\Metadata;

use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;
use Amasty\QuoteAttributes\Model\Metadata\Form;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractIntegrity;

class FormTest extends AbstractIntegrity
{
    /**
     * @dataProvider validateDataDataProvider
     * @magentoDataFixture Amasty_QuoteAttributes::Test/Integration/_files/quote_entity.php
     * @magentoDataFixture Amasty_QuoteAttributes::Test/Integration/_files/attributes.php
     */
    public function testValidateData(array $attributeValues, int $expectedCountErrors): void
    {
        /** @var QuoteEntityRepositoryInterface $quoteEntityRepository */
        $quoteEntityRepository = Bootstrap::getObjectManager()->create(QuoteEntityRepositoryInterface::class);
        $quoteEntity = $quoteEntityRepository->get(1);

        $form = Bootstrap::getObjectManager()->create(Form::class, [
            'quoteEntity' => $quoteEntity,
            'isAjaxRequest' => false
        ]);
        $actualErrors = $form->validateData($attributeValues);
        $actualCountErrors = count($actualErrors);

        $this->assertEquals($expectedCountErrors, $actualCountErrors);
    }

    /**
     * If some of required attributes dont passed in array - mean error.
     *
     * @return array
     */
    public function validateDataDataProvider(): array
    {
        return [
            [
                [
                    'amasty_quote_attribute_1' => '1'
                ],
                2
            ],
            [
                [
                    'amasty_quote_attribute_1' => 'test',
                    'amasty_quote_attribute_2' => 'test'
                ],
                3
            ],
            [
                [
                    'amasty_quote_attribute_1' => 'test',
                    'amasty_quote_attribute_2' => 'test',
                    'amasty_quote_attribute_3' => 'test',
                    'amasty_quote_attribute_6' => 'adsasd12321dasd*'
                ],
                5
            ],
            [
                [
                    'amasty_quote_attribute_1' => 'test',
                    'amasty_quote_attribute_2' => 'test',
                    'amasty_quote_attribute_3' => 'test',
                    'amasty_quote_attribute_4' => '',
                    'amasty_quote_attribute_6' => 'adsasd12321dasd'
                ],
                4
            ],
            [
                [
                    'amasty_quote_attribute_1' => 'test',
                    'amasty_quote_attribute_2' => 'test',
                    'amasty_quote_attribute_3' => 'test',
                    'amasty_quote_attribute_4' => '',
                    'amasty_quote_attribute_6' => 'adsasd12321dasd',
                    'amasty_quote_attribute_7' => 'adsasd12321dasd',
                ],
                4
            ]
        ];
    }
}
