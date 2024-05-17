<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Plugin\RequestQuote\Model\Email\Sender;

use Amasty\QuoteAttributes\Block\Quote\Attributes as QuoteAttributesBlock;
use Amasty\RequestQuote\Model\ConfigProvider;
use Amasty\RequestQuote\Model\Email\Sender;
use Magento\Framework\View\LayoutInterface;

class AddAttributesVariable
{
    public const QUOTE_ATTRIBUTES = 'quote_attributes';

    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * @param Sender $subject
     * @param string $configPath
     * @param string|null $sendTo
     * @param array $data
     * @param int|null $notificationTemplateId
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSendEmail(
        Sender $subject,
        string $configPath,
        ?string $sendTo = null,
        array $data = [],
        int $notificationTemplateId = null
    ): array {
        if (in_array(
            $configPath,
            [ConfigProvider::CONFIG_PATH_CUSTOMER_SUBMIT_EMAIL, ConfigProvider::CONFIG_PATH_CUSTOMER_APPROVE_EMAIL]
        ) && isset($data['quote'])) {
            /** @var QuoteAttributesBlock $quoteAttributesBlock */
            $quoteAttributesBlock = $this->layout->createBlock(QuoteAttributesBlock::class);
            $quoteAttributesBlock->setQuote($data['quote']);
            $data[self::QUOTE_ATTRIBUTES] = $quoteAttributesBlock->toHtml();
        }

        return [$configPath, $sendTo, $data, $notificationTemplateId];
    }
}
