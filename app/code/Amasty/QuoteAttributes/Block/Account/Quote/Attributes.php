<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Block\Account\Quote;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Metadata\Form;
use Amasty\QuoteAttributes\Model\Metadata\FormFactory;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Attributes extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_QuoteAttributes::account/quote/attributes.phtml';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Form
     */
    private $form;

    public function __construct(
        Registry $registry,
        FormFactory $formFactory,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->formFactory = $formFactory;
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
                'label' => $attribute->getStoreLabel(),
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

    private function getQuote(): QuoteInterface
    {
        return $this->registry->registry(RegistryConstants::AMASTY_QUOTE);
    }
}
