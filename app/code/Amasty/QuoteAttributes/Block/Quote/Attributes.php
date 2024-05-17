<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Block\Quote;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Metadata\Form;
use Amasty\QuoteAttributes\Model\Metadata\FormFactory;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block used for email & pdf.
 */
class Attributes extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_QuoteAttributes::quote/attributes.phtml';

    /**
     * @var QuoteInterface
     */
    private $quote;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Form
     */
    private $form;

    public function __construct(FormFactory $formFactory, Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->formFactory = $formFactory;
    }

    /**
     * @param QuoteInterface $quote
     * @return void
     */
    public function setQuote(QuoteInterface $quote): void
    {
        $this->quote = $quote;
    }

    /**
     * @return QuoteInterface
     */
    public function getQuote(): QuoteInterface
    {
        return $this->quote;
    }

    /**
     * @return array
     */
    public function getAttributesData(): array
    {
        $attributesData = [];

        $outputData = $this->getForm()->outputData(AttributeDataFactory::OUTPUT_FORMAT_HTML);
        foreach ($outputData as $attributeCode => $value) {
            /** @var AttributeInterface|Attribute $attribute */
            $attribute = $this->getForm()->getAttribute($attributeCode);
            if ($attribute === null
                || (!$attribute->getIsRequired() && !$value)
            ) {
                continue;
            }

            $attributesData[] = [
                'label' => $attribute->getStoreLabel($this->getQuote()->getStoreId()),
                'value' => $value
            ];
        }

        return $attributesData;
    }

    private function getForm(): Form
    {
        if ($this->form === null) {
            $this->form = $this->formFactory->create([
                'quoteEntity' => $this->getQuote()->getExtensionAttributes()->getQuoteEntity(),
                'isAjaxRequest' => false
            ]);
        }

        return $this->form;
    }
}
