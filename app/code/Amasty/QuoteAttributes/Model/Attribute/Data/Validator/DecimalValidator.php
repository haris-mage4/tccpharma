<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Data\Validator;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Di\ClassExistsWrapper;
use Laminas\I18n\Validator\IsFloat;
use Magento\Framework\App\ObjectManager;

class DecimalValidator
{
    public const INVALID   = 'floatInvalid';

    public const NOT_FLOAT = 'notFloat';

    /**
     * @var ClassExistsWrapper
     */
    private $floatValidator;

    public function __construct(
        ClassExistsWrapper $floatValidator = null // TODO move to not optional
    ) {
        $this->floatValidator = $floatValidator ?? ObjectManager::getInstance()->get(ClassExistsWrapper::class);
    }

    /**
     * @param AttributeInterface|Attribute $attribute
     * @param string $value
     * @return array
     */
    public function validate(AttributeInterface $attribute, string $value): array
    {
        $label = $attribute->getStoreLabel();

        if (class_exists(IsFloat::class)) {
            $validator = new IsFloat();
        } else {
            // Compatibility with m2.4.5 and less. Class 'Laminas\I18n\Validator\IsFloat' doesn't exist.
            // Use Wrapper to fix 'Magento marketplace ruleset' pipeline
            $validator = $this->floatValidator;
        }

        $validator->setMessage(__('"%1" field invalid type entered.', $label), self::INVALID);
        $validator->setMessage(
            __('"%1" field contains non-decimal characters.', $label),
            self::NOT_FLOAT
        );
        $validator->isValid($value);

        return ($validator->getMessages() === false) ? [] :  $validator->getMessages();
    }
}
