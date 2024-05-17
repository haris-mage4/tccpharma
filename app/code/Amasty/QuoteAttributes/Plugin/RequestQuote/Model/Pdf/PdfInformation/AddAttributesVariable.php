<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Plugin\RequestQuote\Model\Pdf\PdfInformation;

use Amasty\QuoteAttributes\Block\Quote\Attributes as QuoteAttributesBlock;
use Amasty\QuoteAttributes\Plugin\RequestQuote\Model\Source\PdfVariables\AddAttributesVariable as VariableSource;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Pdf\PdfInformation;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Framework\View\LayoutInterface;

class AddAttributesVariable
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(Registry $registry, LayoutInterface $layout)
    {
        $this->registry = $registry;
        $this->layout = $layout;
    }

    /**
     * @param PdfInformation $subject
     * @param array $quoteData
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuoteDataForPdf(PdfInformation $subject, array $quoteData): array
    {
        /** @var QuoteInterface $quote */
        $quote = $this->registry->registry(RegistryConstants::AMASTY_QUOTE);
        if ($quote) {
            /** @var QuoteAttributesBlock $quoteAttributesBlock */
            $quoteAttributesBlock = $this->layout->createBlock(QuoteAttributesBlock::class);
            $quoteAttributesBlock->setQuote($quote);
            $quoteData[VariableSource::QUOTE_ATTRIBUTES] = $quoteAttributesBlock->toHtml();
        }

        return $quoteData;
    }
}
