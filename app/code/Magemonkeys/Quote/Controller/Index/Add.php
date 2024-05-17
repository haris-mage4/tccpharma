<?php
/**
 * @override
 */
namespace Magemonkeys\Quote\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\RequestQuote\Model\QuoteFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magemonkeys\Customerinfo\Helper\Data;
use Stathmos\QuoteCommunication\Model\RemarkCommentFactory;
use Zend_Log_Exception;

class Add extends \Stathmos\QuoteCommunication\Controller\Index\Add
{
    protected RemarkCommentFactory $remarkComment;
    protected ResultFactory $resultRedirect;
    private Session $customerSession;
    private TransportBuilder $transportBuilder;
    private State $state;
    private StoreManagerInterface $storeManager;
    private QuoteFactory $quoteFactory;
    private TimezoneInterface $timezone;
    private DateTime $dateTime;
    private Data $_helper;

    public function __construct(
        Context               $context,
        RemarkCommentFactory  $remarkCommentFactory,
        Session               $customerSession,
        ResultFactory         $resultFactory,
        TransportBuilder      $transportBuilder,
        State                 $state,
        StoreManagerInterface $storeManager,
        QuoteFactory          $quoteFactory,
        TimezoneInterface     $timezone,
        DateTime              $dateTime,
        Data                  $_helper
    ) {
        parent::__construct($context, $remarkCommentFactory, $customerSession, $resultFactory);
        $this->remarkComment = $remarkCommentFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirect = $resultFactory;
        $this->transportBuilder = $transportBuilder;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
        $this->_helper = $_helper;
    }

    /**
     * @throws NoSuchEntityException
     * @throws Zend_Log_Exception
     */
    public function execute()
    {
        $customerQuoteAttribute = $this->_helper->getAdditionalData($this->getCurrentCustomerId());
        $allAttributeData = $customerQuoteAttribute['all_attribute_data'] ?? '';
        $facilityName = $customerQuoteAttribute['facility_name'] ?? '';

        $postParams = $this->getRequest()->getParams();
        $quoteFactory = $this->quoteFactory->create()->load($postParams['quote_id']);
        $formattedDate = $this->timezone->formatDateTime(
            $quoteFactory->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::SHORT
        );
        $salesEmail = $this->storeManager->getStore()->getConfig('trans_email/ident_sales/email');
        $salesName = $this->storeManager->getStore()->getConfig('trans_email/ident_sales/name');
        $remarkCommentModel = $this->remarkComment->create();
        $collection = $this->remarkComment->create()->getCollection()->addFieldToFilter('quote_id', ['eq' => $postParams['quote_id']]);
        $remarkHistory = '';

        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        try {
            if (empty($postParams['quote_id'])) {
                return $resultRedirect;
            }

            $remarkCommentModel->addData([
                "quote_id" => $postParams['quote_id'],
                "customer_id" => $this->getCurrentCustomerId(),
                "customer_name" => $this->getCurrentCustomerName(),
                "remark_comment" => $postParams['remark_comment'],
            ]);
            $remarkCommentsaveData = $remarkCommentModel->save();
            $remarkHistory .= "<table style='width:100%;border: 1px solid #c7c3c3;'>";
            $remarkHistory .= "<th style='text-align:center;font-size:18px;padding:10px;border-bottom: 1px solid #c7c3c3; background-color: yellow;'> Remark History </th>";

            foreach ($collection as $data) {
                $createdAt = $data->getCreatedAt();
                $createdAtPST = new \DateTime($createdAt, new \DateTimeZone('UTC'));
                $createdAtPST->setTimezone(new \DateTimeZone('America/Phoenix'));
                $formattedDate = $createdAtPST->format('M d, Y, h:i:s A');

                $remarkHistory .= "<tr style='font-weight:bold;'>";
                if ($data->getAdminUserId() != null) {
                    $remarkHistory .= "<td style='float: right; background: red; padding: 10px; border-radius: 7px; margin: 10px; background:#B4F2BB52'>";
                    $remarkHistory .= "<span style='color:#687e83;font-size: 12px;'>" . $data->getAdminUserName() . "</span><br>";
                } else {
                    $remarkHistory .= "<td style='padding: 10px; border-radius: 7px; float: left; margin: 10px; background:#ECFAFF'>";
                    $remarkHistory .= "<span style='color:#687e83;font-size: 12px;'>" . $data->getCustomerName() . "</span><br>";
                }
                $remarkHistory .= "<span style='color:#687e83;font-size: 12px;'>" . $formattedDate . "</span><br>";
                $remarkHistory .= $data->getRemarkComment();
                $remarkHistory .= "</td>";
                $remarkHistory .= "</tr>";
            }

            $remarkHistory .= "</table>";
            $data = [
                'increment_id' => $quoteFactory->getIncrementId(),
                'customerName' => $salesName,
                'quote_id' => $postParams['quote_id'],
                'created_at' => $formattedDate,
                'remark_comment' => $postParams['remark_comment'],
                'remark_history' => $remarkHistory,
                'submit_fields' => $allAttributeData,
                'facility_name' => $facilityName,
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $sender = [
                'name' => $this->customerSession->getCustomer()->getName(),
                'email' => $this->customerSession->getCustomer()->getEmail(),
            ];
            $receiver = ['name' => $salesName, 'email' => $salesEmail];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier(38)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($data)
                ->setFrom($sender)
                ->addTo($receiver['email'], $receiver['name'])
                ->getTransport();

            $transport->sendMessage();

            if ($remarkCommentsaveData) {
                $this->messageManager->addSuccess(__('Your representative has been notified of your remark!'));
            }
        } catch (\Exception $e) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
            $zendLogger = new \Zend_Log();
            $zendLogger->addWriter($writer);
            $zendLogger->info($e->getMessage());
        }
        return $resultRedirect;
    }

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
