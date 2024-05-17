<?php

namespace Amasty\RequestQuote\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Amasty\RequestQuote\Model\Quote\Backend\Session as QuoteSession;
use Amasty\RequestQuote\Model\Email\Sender;
use Amasty\RequestQuote\Model\Quote\Backend\Edit as BackendQuoteEdit;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;

class Sendmail extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var Sender
     */
    private $emailSender;

    /**
     * @var BackendQuoteEdit
     */
    private $backendQuoteEdit;
    
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        QuoteSession $quoteSession,
        Sender $emailSender,
        BackendQuoteEdit $backendQuoteEdit,
        QuoteRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteSession = $quoteSession;
        $this->emailSender = $emailSender;
        $this->backendQuoteEdit = $backendQuoteEdit;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $quote = $this->quoteRepository->get($quoteId);
            $this->emailSender->sendQuoteEditEmail($quote);
            $this->messageManager->addSuccessMessage(__('Email sent successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while sending the email.'));
        }

        return $this->_redirect('*/*/edit', ['quote_id' => $quoteId]);
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    protected function getSession()
    {
        return $this->quoteSession;
    }
}
