<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\Ordernotes\Plugin\Model\Checkout;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Filter\FilterManager;
use Magento\Quote\Model\QuoteRepository;

class PaymentInformationManagement
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;
    
    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @param JsonHelper $jsonHelper
     * @param FilterManager $filterManager
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        JsonHelper $jsonHelper,
        FilterManager $filterManager,
        QuoteRepository $quoteRepository
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->filterManager = $filterManager;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param PaymentInformationManagement $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @return void
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ) {
        $orderComment = $paymentMethod->getExtensionAttributes();
        if ($orderComment->getComment()) {
            $comment = trim($orderComment->getComment());
        } else {
            $comment = '';
        }
        
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setUmOrderComment($comment);
    }
}
