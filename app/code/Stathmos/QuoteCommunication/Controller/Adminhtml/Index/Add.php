<?php
namespace Stathmos\QuoteCommunication\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;
use Stathmos\QuoteCommunication\Model\RemarkCommentFactory;

class Add extends Action
{
    protected PageFactory $resultPageFactory;
    private RemarkCommentFactory $remarkComment;
    private Session $authSession;
    private QuoteFactory $quoteFactory;

    public function __construct(
        Context $context,
        RemarkCommentFactory  $RemarkCommentFactory,
        Session $authSession,
        QuoteFactory $quoteFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->remarkComment = $RemarkCommentFactory;
        $this->authSession = $authSession;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        $postParams = $this->getRequest()->getParams();

        if(isset($postParams['quote_id']) && empty($postParams['quote_id'])){
            return $resultRedirect;
        }
        $quoteData = $this->getQuoteById($postParams['quote_id']);
        $quoteCustomerId = $quoteData->getCustomerId();
        $quoteCustomerName = $quoteData->getCustomerFirstname()." ".$quoteData->getCustomerLastname();
        $adminUserName = $this->authSession->getUser()->getUsername();
        $adminUserId = $this->authSession->getUser()->getId();
        $remarkCommentModel = $this->remarkComment->create();
        $remarkCommentModel->addData([
            "quote_id" => $postParams['quote_id'],
            "admin_user_id" => $adminUserId,
            "admin_user_name" => $adminUserName,
            "customer_id" => $quoteCustomerId,
            "customer_name" => $quoteCustomerName,
            "remark_comment" => $postParams['remark_comment']
            ]);
        $remarkCommentsaveData = $remarkCommentModel->save();
        if($remarkCommentsaveData){
            $this->messageManager->addSuccessMessage( __('Added Remark Comment Successfully !') );
        }
        return $resultRedirect;
    }

    public function getQuoteById($quoteId)
    {
        return $this->quoteFactory->create()->load($quoteId);

    }

}
