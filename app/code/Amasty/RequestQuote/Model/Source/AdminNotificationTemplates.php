<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AdminNotificationTemplates implements OptionSourceInterface
{
    private $scopeConfig;
    private $submittedQuote;
    private $approvedQuote;
    private $modifiedQuote;
    private $canceledQuote;
    private $expiredQuote;
    private $reminder;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        $this->submittedQuote = $this->scopeConfig->getValue('amasty_request_quote/customer_notifications/customer_template_submit');
        $this->approvedQuote = $this->scopeConfig->getValue('amasty_request_quote/customer_notifications/customer_template_approve');
        $this->modifiedQuote = $this->scopeConfig->getValue('amasty_request_quote/customer_notifications/customer_template_edit_quote');
        $this->canceledQuote = $this->scopeConfig->getValue('amasty_request_quote/customer_notifications/customer_template_cancel');
        $this->expiredQuote = $this->scopeConfig->getValue('amasty_request_quote/customer_notifications/customer_template_expired');
        $this->reminder = $this->scopeConfig->getValue('amasty_request_quote/customer_notifications/customer_template_reminder');
    }

    public function toOptionArray(): array
    {
        return [
            [
                'value' => $this->submittedQuote,
                'label' => __('Submitted Quote')
            ],
            [
                'value' => $this->approvedQuote,
                'label' => __('Approved Quote')
            ],
            [
                'value' => $this->modifiedQuote,
                'label' => __('Modified Quote')
            ],
            [
                'value' => $this->canceledQuote,
                'label' => __('Canceled Quote')
            ],
            [
                'value' => $this->expiredQuote,
                'label' => __('Expired Quote')
            ],
            [
                'value' => $this->reminder,
                'label' => __('Reminder')
            ]
        ];
    }
}
