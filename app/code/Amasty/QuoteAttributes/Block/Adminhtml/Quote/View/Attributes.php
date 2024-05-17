<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Block\Adminhtml\Quote\View;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Metadata\Form;
use Amasty\QuoteAttributes\Model\Metadata\FormFactory;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote\Backend\Session;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Eav\Model\AttributeDataFactory;

class Attributes extends Template
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Form
     */
    private $form;

    /**
     * @var Session
     */
    private $backendSession;

    public function __construct(
        Session $backendSession,
        FormFactory $formFactory,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formFactory = $formFactory;
        $this->backendSession = $backendSession;
    }

    /**
     * @return array
     */
    public function getAttributesData(): array
    {
        $attributesData = [];

        $outputData = $this->getForm()->outputData(
            AttributeDataFactory::OUTPUT_FORMAT_HTML,
            $this->getQuote()->getStoreId()
        );
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

    private function getQuote(): QuoteInterface
    {
        return $this->backendSession->getQuote();
    }
}
