<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\Ordernotes\Plugin\Model\Checkout;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Filter\FilterManager;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestPaymentInformationManagement
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

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
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        JsonHelper $jsonHelper,
        FilterManager $filterManager,
        QuoteRepository $quoteRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->filterManager = $filterManager;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param GuestPaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param $email
     * @param PaymentInterface $paymentMethod
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ) {
        $orderComment = $paymentMethod->getExtensionAttributes();
        if ($orderComment->getComment()):
            $comment = trim($orderComment->getComment());
            $orderComment = $this->filterManager->stripTags($comment);
                $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
                $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
                $quote->setUmOrderComment($orderComment);
        endif;
    }
}
