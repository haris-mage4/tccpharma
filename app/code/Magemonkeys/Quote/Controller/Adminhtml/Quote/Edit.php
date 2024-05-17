<?php
namespace Magemonkeys\Quote\Controller\Adminhtml\Quote;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\QuoteFactory;
use Magemonkeys\Quote\Model\QuoteFactory as MageQuoteFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutFactory;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Stathmos\QuoteCommunication\Model\RemarkCommentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magemonkeys\Customerinfo\Helper\Data;

/**
* Class Index
* @package PandaGroup\MyAdminController\Controller\Adminhtml\Blog
*/
class Edit extends \Magento\Framework\App\Action\Action
{
    protected $transportBuilder;
    protected $inlineTranslation;

	public function __construct(
	    Context $context,
	    JsonFactory $resultJsonFactory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        QuoteRepositoryInterface $quote_repository,
        QuoteFactory $quote_factory,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        MageQuoteFactory $mage_quote_factory,
        PageFactory $pageFactory,
        UrlResolver $urlResolver,
        RemarkCommentFactory  $RemarkCommentFactory,
        Session $authSession,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        Data $_helper

        )
    {
	    parent::__construct($context);
	    $this->resultJsonFactory = $resultJsonFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->quote_repository = $quote_repository;
        $this->quote_factory = $quote_factory;
        $this->resultFactory = $resultFactory;
        $this->_pageFactory = $pageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->mage_quote_factory = $mage_quote_factory;
        $this->urlResolver = $urlResolver;
        $this->remarkComment = $RemarkCommentFactory;
        $this->authSession = $authSession;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->_helper = $_helper;



    }

 public function execute()
    {

        $post = $this->getRequest()->getPostValue();
        $admin_remark = '';
        if(array_key_exists('quote',$post)){
            $admin_remark = $post['quote']['note'];
        }
        $adminUserName = $this->authSession->getUser()->getUsername();
        $adminUserId = $this->authSession->getUser()->getId();
        $quote_id = $this->getRequest()->getParam('quote_id');
        $quote_repo = $this->quote_repository->get($quote_id);
        $customer_quote_attribute = $this->_helper->getAdditionalData($quote_repo->getCustomerId());
        $resultJson = $this->resultJsonFactory->create();
        $collection = $this->remarkComment->create()->getCollection()->addFieldToFilter('quote_id', ['eq' => $quote_id]);
        $remark_history = '';
        $storeId = $this->storeManager->getStore()->getId();
        if (array_key_exists("history",$post)){
            $is_customer_notified = $post['history']['is_customer_notified'];
        }else{
            $is_customer_notified = false;
        }
        if($admin_remark != ''){
            if($is_customer_notified == 1){
                try {

                    $model = $this->quote_factory->create()->load($quote_id);
                    $remark = $model->getData('remarks');
                    $remark_data = json_decode($remark,true);
                    $remark_data['admin_note_remark'] = $admin_remark;

                    $model->setData('remarks',json_encode($remark_data));
                    $model->save();
                    $mage_model = $this->mage_quote_factory->create();
                    $mage_model->setData('quote_id',$quote_id);
                    $mage_model->setData('admin_remark',$admin_remark);
                    $mage_model->setData('is_customer_notified',1);
                    $mage_model->save();
                    $remarkCommentModel = $this->remarkComment->create();
		            $remarkCommentModel->addData([
			        "quote_id" => $quote_id,
			        "customer_id" => $quote_repo->getCustomerId(),
			        "customer_name" => $quote_repo->getCustomerName(),
			        "remark_comment" => $admin_remark,
                    "admin_user_id" => $adminUserId,
                    "admin_user_name" => $adminUserName
            	    ]);
                    $remarkCommentsaveData = $remarkCommentModel->save();

                    $this->inlineTranslation->suspend();
                    $receiver = [
                        'name' => $quote_repo->getCustomerName(),
                        'email' => $quote_repo->getCustomerEmail()
                    ];
                    $currentDateTime = $this->dateTime->gmtDate(); // Get current GMT date and time
                    $remark_history .= "<table style='width:100%;border: 1px solid #c7c3c3;'>";
                    $remark_history .= "<th style='text-align:center;font-size:18px;padding:10px;border-bottom: 1px solid #c7c3c3;'> Remark History </th>";
                    foreach($collection as $data){

                        if($data->getAdminUserId() == null){
                            $remark_history .= "<tr style='font-weight:bold;'>";
                            $remark_history .= "<td style='float: right;
                            background: red;
                            padding: 10px;
                            border-radius: 7px;
                            margin: 10px; background:#B4F2BB52'>";
                            $remark_history .= "<span style='color:#687e83;font-size: 12px;'>".$this->dateTime->date('M d, Y, h:i:s A', $data->getCreatedAt())."</span><br>";
                            $remark_history .= $data->getRemarkComment();
                        }else{
                            $remark_history .= "<tr style='font-weight:bold;'>";
                            $remark_history .= "<td style='padding: 10px;
                            border-radius: 7px;
                            float: left;
                            margin: 10px;
                            background:#ECFAFF'>";
                            $remark_history .= "<span style='color:#687e83;font-size: 12px;'>".$this->dateTime->date('M d, Y, h:i:s A', $data->getCreatedAt())."</span><br>";
                            $remark_history .= $data->getRemarkComment();

                        }
                        $remark_history .= "</td>";
                        $remark_history .= "</tr>";
                    }
                    $remark_history .= "</table>";
                    $created_at = $this->dateTime->date('M d, Y, h:i:s A', $quote_repo->getCreatedAt());
                    $templateVars = [
                        'name' => $quote_repo->getCustomerName(),
                        'quote_id' => $quote_id,
                        'increment_id' => $quote_repo->getIncrementId(),
                        'admin_remark'=> $admin_remark,
                        'viewUrl' => $this->urlResolver->getViewUrl((int) $quote_id, ['_nosid' => true]),
                        'remark_history' => $remark_history,
                        'submit_fields' => $customer_quote_attribute['all_attribute_data'],
                        'facility_name' => $customer_quote_attribute['facility_name'],
                        'created_at' => $created_at


                    ];
                    $this->transportBuilder->setTemplateIdentifier('39')
                        ->setTemplateOptions([
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $storeId


                        ])
                        ->setTemplateVars($templateVars)
                        ->setFrom('sales')
                        ->addTo($receiver['email'], $receiver['name']);
                    $transport = $this->transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                    return $this->_pageFactory->create();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage('Could not send email: ' . $e->getMessage());
                    return $this->_pageFactory->create();
                }

                return $this->_pageFactory->create();
            }else{
                $model = $this->quote_factory->create()->load($quote_id);
                    $remark = $model->getData('remarks');
                    $remark_data = json_decode($remark,true);
                    $remark_data['admin_note_remark'] = $admin_remark;

                    $model->setData('remarks',json_encode($remark_data));
                    $model->save();
                $mage_model = $this->mage_quote_factory->create();
                $mage_model->setData('quote_id',$quote_id);
                $mage_model->setData('admin_remark',$admin_remark);
                $mage_model->save();
                $remarkCommentModel = $this->remarkComment->create();
		            $remarkCommentModel->addData([
			        "quote_id" => $quote_id,
			        "customer_id" => $quote_repo->getCustomerId(),
			        "customer_name" => $quote_repo->getCustomerName(),
			        "remark_comment" => $admin_remark,
                    "admin_user_id" => $adminUserId,
                    "admin_user_name" => $adminUserName
            	    ]);
                $remarkCommentsaveData = $remarkCommentModel->save();
                return $this->_pageFactory->create();
            }
        }else{
            $success = true;
            return $resultJson->setData([
            'error' => true,
            'message' => 'Please Add  Remark'
            ]);
          return $this->_pageFactory->create();
        }

    }
}
