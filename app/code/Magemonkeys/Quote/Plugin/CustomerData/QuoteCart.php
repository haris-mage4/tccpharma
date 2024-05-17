<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Magemonkeys\Quote\Plugin\CustomerData;

class QuoteCart
{
   
    public function afterGetSectionData(
        \Amasty\RequestQuote\CustomerData\QuoteCart $subject,
        $result
    ) {
        $totals = $subject->getQuote()->getTotals();
        $subtotalAmount = $totals['subtotal']->getValue();

        $subtotal = isset($totals['subtotal']) ? $subject->checkoutHelper->formatPrice($subtotalAmount) : 0;
        $subtotal = max($subtotal, 1); // Ensure the subtotal is at least 1

        $result['subtotal'] = 100;

        return $result;
    }
}