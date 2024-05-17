<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Plugin\Email\Model\Template;

use Magento\Email\Model\Template;

class AddAttributesVariable
{
    private const APPLICABLE_TEMPLATES = [
        'amasty_request_quote_customer_notifications_customer_template_submit',
        'amasty_request_quote_customer_notifications_customer_template_approve'
    ];

    /**
     * @param Template $subject
     * @param array $result
     * @param bool $withGroup
     * @return array
     */
    public function afterGetVariablesOptionArray(Template $subject, array $result, bool $withGroup = false): array
    {
        if (in_array($subject->getOrigTemplateCode(), self::APPLICABLE_TEMPLATES, true)) {
            $quoteAttributesVariable = [
                'value' => '{{var quote_attributes|raw}}',
                'label' => __('Quote Attributes')
            ];

            if ($withGroup) {
                $result['value'][] = $quoteAttributesVariable;
            } else {
                $result[] = $quoteAttributesVariable;
            }
        }

        return $result;
    }
}
