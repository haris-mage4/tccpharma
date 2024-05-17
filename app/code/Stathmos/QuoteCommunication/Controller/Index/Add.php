<?php
namespace Stathmos\QuoteCommunication\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Stathmos\QuoteCommunication\Model\RemarkCommentFactory;

class Add extends Action
{

    protected RemarkCommentFactory $remarkComment;
    protected ResultFactory $resultRedirect;
    private Session $customerSession;

    /**
     * @param Context $context
     * @param RemarkCommentFactory $RemarkCommentFactory
     * @param Session $customerSession
     * @param ResultFactory $result
     */
    public function __construct(
        Context                         $context,
        RemarkCommentFactory            $RemarkCommentFactory,
        Session                         $customerSession,
        ResultFactory                   $result
    )
    {
        parent::__construct($context);
        $this->remarkComment = $RemarkCommentFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirect = $result;
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        $postParams = $this->getRequest()->getParams();

        if(isset($postParams['quote_id']) && empty($postParams['quote_id'])){
            return $resultRedirect;
        }

        $remarkCommentModel = $this->remarkComment->create();
        $remarkCommentModel->addData([
            "quote_id" => $postParams['quote_id'],
            "customer_id" => $this->getCurrentCustomerId(),
            "customer_name" => $this->getCurrentCustomerName(),
            "remark_comment" => $postParams['remark_comment']
        ]);
        $remarkCommentsaveData = $remarkCommentModel->save();
        if($remarkCommentsaveData){
            $this->messageManager->addSuccess( __('Your representative has been notified of your remark!') );
        }
        return $resultRedirect;
    }

    /**
     * @return mixed
     */
    public function getCurrentCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }

    /**
     * @throws LocalizedException
     */
    public function getCurrentCustomerName(): string
    {
        return $this->customerSession->getCustomer()->getName();
    }
}

