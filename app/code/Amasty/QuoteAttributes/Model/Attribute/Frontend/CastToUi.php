<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Frontend;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Attribute\Data\Validator\CastValidationRuleToUiRule;
use Amasty\QuoteAttributes\Model\Attribute\Frontend\Modifier\ModifierInterface;

class CastToUi implements CastToUiInterface
{
    /**
     * @var GetUiMeta
     */
    private $getUiMeta;

    /**
     * @var ModifierInterface
     */
    private $uiConfigModifier;

    /**
     * @var CastValidationRuleToUiRule
     */
    private $castValidationRuleToUiRule;

    public function __construct(
        GetUiMeta $getUiMeta,
        ModifierInterface $uiConfigModifier,
        CastValidationRuleToUiRule $castValidationRuleToUiRule
    ) {
        $this->getUiMeta = $getUiMeta;
        $this->uiConfigModifier = $uiConfigModifier;
        $this->castValidationRuleToUiRule = $castValidationRuleToUiRule;
    }

    /**
     * @param AttributeInterface|Attribute $attribute
     * @return array
     */
    public function execute(AttributeInterface $attribute): array
    {
        $uiConfig = $this->getUiMeta->execute($attribute->getFrontendInput());

        $uiConfig['dataScope'] = $attribute->getAttributeCode();

        $uiConfig['config']['label'] = $attribute->getAttributeCode();
        $uiConfig['config']['label'] = $attribute->getStoreLabel();
        if ($attribute->getIsRequired()) {
            $uiConfig['config']['validation']['required-entry'] = true;
        }
        if (in_array($attribute->getFrontendInput(), ['text', 'textarea'])) {
            $uiConfig['config']['validation']['validate-no-html-tags'] = true;
        }

        foreach ($attribute->getValidateRules() as $validationName => $ruleName) {
            if ($validationName === 'input_validation') {
                $uiRuleName = $this->castValidationRuleToUiRule->execute($ruleName);
                $uiConfig['config']['validation'][$uiRuleName] = true;
            }
        }
        $uiConfig['config'] = $this->uiConfigModifier->execute($attribute, $uiConfig['config']);

        return $uiConfig;
    }
}
