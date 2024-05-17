<?php
declare(strict_types=1);

namespace Stathmos\QuoteCommunication\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Stathmos\QuoteCommunication\Model\RemarkCommentFactory;

class RemarkComment extends Template
{
    /**
     * @var Http
     */
    protected Http $request;
    /**
     * @var Session
     */
    protected Session $customerSession;
    /**
     * @var RemarkCommentFactory
     */
    protected RemarkCommentFactory $remarkCommentFactory;

    /**
     * @param Context $context
     * @param Http $request
     * @param Session $customerSession
     * @param RemarkCommentFactory $RemarkCommentFactory
     */
    public function __construct(
        Context $context,
        Http $request,
        Session $customerSession,
        RemarkCommentFactory $RemarkCommentFactory
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->remarkCommentFactory = $RemarkCommentFactory;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function getRemarkCommentCollection()
    {
        $quoteId = $this->getQuoteId();
        $customerId = $this->getCurrentCustomerId();

        if ($quoteId && $customerId) {
            $remarkComment = $this->remarkCommentFactory->create();
            return $remarkComment->getCollection()->addFieldToFilter('quote_id', ['eq' => $quoteId]);
        }
    }

    /**
     * @return mixed
     */
    public function getCurrentCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }

    /**
     * @return string
     */
    public function getFormAction(): string
    {
        return $this->getUrl('remarkcomment/index/add', ['_secure' => true]);
    }

    /**
     * @return false|mixed
     */
    public function getQuoteId()
    {
        $quoteParams = $this->request->getParams();
        if (isset($quoteParams['quote_id'])) {
            return $quoteParams['quote_id'];
        }
        return false;
    }
}
