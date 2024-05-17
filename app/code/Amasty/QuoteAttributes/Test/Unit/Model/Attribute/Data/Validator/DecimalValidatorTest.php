<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Test\Unit\Model\Attribute\Data\Validator;

use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Attribute\Data\Validator\DecimalValidator;
use PHPUnit\Framework\TestCase;
use Amasty\QuoteAttributes\Model\Di\ClassExistsWrapper;

class DecimalValidatorTest extends TestCase
{
    /**
     * @covers DecimalValidator::validate
     *
     * @dataProvider validateDataProvider
     *
     * @param string $value
     * @param bool $expectedResult
     * @return void
     */
    public function testValidate(string $value, bool $expectedResult, array $messages): void
    {
        $attribute = $this->createMock(Attribute::class);
        $attribute->expects($this->any())->method('getStoreLabel')->willReturn('');
        $wrapperMock = $this->createMock(ClassExistsWrapper::class);
        $wrapperMock
            ->expects($this->any())
            ->method('__call')
            ->withConsecutive(
                ['setMessage'],
                ['setMessage'],
                ['isValid'],
                ['getMessages'],
                ['getMessages']
            )->willReturnOnConsecutiveCalls(null, null, $expectedResult, $messages, $messages);

        $decimalValidator = new DecimalValidator($wrapperMock);
        $actualResult = empty($decimalValidator->validate($attribute, $value));

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            ['1', true, []],
            ['1.2', true, []],
            ['asdas', false, ['invalid number']],
            ['01-01-2000', false, ['invalid number']]
        ];
    }
}
