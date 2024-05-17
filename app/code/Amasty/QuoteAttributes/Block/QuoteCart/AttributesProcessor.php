<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Block\QuoteCart;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute\Frontend\CastToUiInterface;
use Amasty\QuoteAttributes\Model\QuoteEntity\GetAttributeList;
use Amasty\RequestQuote\Model\Quote\Session as QuoteSession;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class AttributesProcessor implements LayoutProcessorInterface
{
    public const QUOTE_ENTITY_SCOPE = 'quote_entity';

    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * @var GetAttributeList
     */
    private $getAttributeList;

    /**
     * @var CastToUiInterface
     */
    private $castToUi;

    public function __construct(
        QuoteSession $quoteSession,
        GetAttributeList $getAttributeList,
        CastToUiInterface $castToUi
    ) {
        $this->quoteSession = $quoteSession;
        $this->getAttributeList = $getAttributeList;
        $this->castToUi = $castToUi;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout): array
    {
        $jsLayout['components']['details']['children']['quote-attributes-provider'] = ['component' => 'uiComponent'];
        $jsLayout['components']['details']['children']['quote-attributes'] = [
            'component' => 'Amasty_QuoteAttributes/js/component/quote-attributes',
            'config' => [
                'displayArea'   => 'quote-attributes',
                'quoteId'       => $this->quoteSession->getQuote()->getId()
            ],
            'dataScope' => sprintf('data.%s', self::QUOTE_ENTITY_SCOPE),
            'provider' => 'details.quote-attributes-provider'
        ];

        $jsLayout['components']['details']['children']['quote-attributes']['children'] = [];
        foreach ($this->getAttributes() as $attribute) {
            $attributeUiConfig = $this->castToUi->execute($attribute);
            $attributeUiConfig['provider'] = 'details.quote-attributes-provider';
            $jsLayout['components']['details']['children']['quote-attributes']['children'][] = $attributeUiConfig;
            $jsLayout['components']['details']['children']['quote-attributes-provider']['config']['data']
                [self::QUOTE_ENTITY_SCOPE][$attribute->getAttributeCode()] = $attribute->getDefaultValue();
        }

        return $jsLayout;
    }

    /**
     * @return AttributeInterface[]
     */
    private function getAttributes(): array
    {
        $quoteEntity = $this->quoteSession->getQuote()->getExtensionAttributes()->getQuoteEntity();
        return $quoteEntity ? $this->getAttributeList->execute($quoteEntity) : [];
    }
}
