<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Email;

use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Helper\Date as DateHelper;
use Amasty\RequestQuote\Model\Notifications\IsNotificationEnabled;
use Amasty\RequestQuote\Model\Pdf\PdfProvider;
use Amasty\RequestQuote\Model\Source\CustomerNotificationTemplates;
use Amasty\RequestQuote\Model\UrlResolver;
use Exception;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Email\Model\BackendTemplate;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemCollectionFactory;


class Sender
{
    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SessionFactory
     */
    private SessionFactory $customerSessionFactory;

    /**
     * @var Emulation
     */
    private Emulation $storeEmulation;

    /**
     * @var DateHelper
     */
    private DateHelper $dateHelper;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var PdfProvider
     */
    private PdfProvider $pdfProvider;

    /**
     * @var LayoutInterface
     */
    private LayoutInterface $layout;

    /**
     * @var IsNotificationEnabled
     */
    private IsNotificationEnabled $isNotificationEnabled;

    /**
     * @var UrlResolver
     */
    private UrlResolver $urlResolver;

    /**
     * @var QuoteItemCollectionFactory
     */
    private QuoteItemCollectionFactory $quoteItemCollectionFactory;

    private TimezoneInterface $timezoneInterface;
    private BackendTemplate $emailTemplate;
    private QuoteEntityRepositoryInterface $quoteEntityRepository;

    public function __construct(
        Data $helper,
        DateHelper $dateHelper,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        SessionFactory $customerSessionFactory,
        Emulation $storeEmulation,
        Registry $registry,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        Serializer $serializer,
        PdfProvider $pdfProvider,
        LayoutInterface $layout,
        IsNotificationEnabled $isNotificationEnabled,
        UrlResolver $urlResolver,
        BackendTemplate $emailTemplate,
        QuoteEntityRepositoryInterface $quoteEntityRepository,
        QuoteItemCollectionFactory $quoteItemCollectionFactory,
        TimezoneInterface $timezoneInterface
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->storeEmulation = $storeEmulation;
        $this->dateHelper = $dateHelper;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->pdfProvider = $pdfProvider;
        $this->layout = $layout;
        $this->isNotificationEnabled = $isNotificationEnabled;
        $this->urlResolver = $urlResolver;
        $this->emailTemplate = $emailTemplate;
        $this->quoteEntityRepository = $quoteEntityRepository;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * @param string $configPath Ex: amasty_request_quote/admin_notifications/notify_template
     * @param string|null $sendTo
     * @param array $data
     * @param int|null $notificationTemplateId
     * @return void
     * @throws NoSuchEntityException|LocalizedException
     */
    public function sendEmail(
        string $configPath,
        string $sendTo = null,
        array $data = [],
        int $notificationTemplateId = null
    ): void {
        if ($notificationTemplateId !== null && !$this->isNotificationEnabled->execute($notificationTemplateId)) {
            return;
        }

        $senderEmail = null;
        $configParts = explode('/', $configPath);
        $store = $this->storeManager->getStore();

        if (isset($configParts[1])) {
            $senderEmail = $this->helper->getSenderEmail($configParts[1], $store->getCode());

            if (!$sendTo) {
                $sendTo = $this->helper->getSendToEmail($configParts[1]);

                if ($sendTo && strpos($sendTo, ',') !== false) {
                    $sendTo = explode(',', $sendTo);
                }
            }
        }

        if ($senderEmail && $sendTo) {
            $html = '';
            $sales_rep_for_all_quote = '';
            $facility_name = '';
            try {
                $attribute_html = '';
                $objectManager = ObjectManager::getInstance();
                $quoteEntityRepository = $this->quoteEntityRepository->getByQuoteId($data['quote']->getId());
                $model = $objectManager->create(\Amasty\QuoteAttributes\Model\QuoteEntity::class)->load($quoteEntityRepository->getEntityId());
                $quote_attribute_data = $model->getData();
                unset($quote_attribute_data['entity_id']);
                unset($quote_attribute_data['quote_id']);
                foreach ($quote_attribute_data as $key => $value) {
                    $attributeCode = $key; // Replace with the actual attribute code
                    $attributeValue = $value;
                    $attributeOptions = $model->getResource()->getAttribute($attributeCode)->getSource()->getAllOptions();
                    $attribute = $model->getResource()->getAttribute($attributeCode);
                    $attributeLabel = $attribute->getDefaultFrontendLabel();
                    if ($attributeCode == 'select_sales_rep') {
                        foreach ($attributeOptions as $option) {
                            if ($option['value'] == $attributeValue) {
                                $attribute_html .= "<span><b>" . $attribute->getDefaultFrontendLabel() . "</b></span> : " . $option['label'] . "<br>";
                                $sales_rep_for_all_quote = "<span><b>" . $attribute->getDefaultFrontendLabel() . "</b></span> : " . $option['label'] . "<br>";
                                break;
                            }
                        }
                    } elseif ($attributeCode == 'target_price') {
                        if (!$attributeValue) {
                            $attribute_html .= "<br><span><b>" . $attributeLabel . "</b></span> : No <br>";
                        } else {
                            $attribute_html .= "<br><span><b>" . $attributeLabel . "</b></span> : Yes <br>";
                        }
                    } else {
                        if ($attribute) {
                            $attributeLabel = $attribute->getDefaultFrontendLabel();
                            foreach ($attributeOptions as $option) {
                                if ($option['value'] == $attributeValue) {
                                    $attribute_html .= "<br><span><b>" . $attributeLabel . "</b></span> : " . $option['label'] . "<br>";
                                    break;
                                }
                            }
                        }
                    }
                    if ($attributeCode == 'facility') {
                        $attributeLabel = $attribute->getDefaultFrontendLabel();
                        $facility_name = $attributeLabel . ' : ' . $attributeValue;
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }

            $quote = $data['quote'];
            $quoteItemId = $quote->getId();
            $quoteItemCollection = $this->quoteItemCollectionFactory->create();
            $quoteItemCollection->addFieldToFilter('quote_id', $quoteItemId);
            $emailSubject = '';
            if ($quoteItemCollection->getSize() > 0) {
                $quoteItem = $quoteItemCollection->getFirstItem();
                $emailSubject = $quoteItem->getData('email_subject');
            }

            $defaultData = [
                'store' => $store,
                'customerName' => $this->getCustomerSession()->getCustomer()->getName(),
                'customerEmail' => $this->getCustomerSession()->getCustomer()->getEmail(),
                'submit_fields' => $attribute_html,
                'sales_rep' => $sales_rep_for_all_quote,
                'facility_name' => $facility_name,
                'custom_email_subject' =>  $emailSubject ,
            ];
            $mailTemplateId = $this->scopeConfig->getValue(
                $configPath,
                ScopeInterface::SCOPE_STORE
            ) ?: str_replace('/', '_', $configPath);

            try {
                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $mailTemplateId
                )->setTemplateModel(
                    Template::class
                )->setTemplateOptions(
                    ['area' => Area::AREA_FRONTEND, 'store' => $store->getId()]
                )->setTemplateVars(
                    array_merge($defaultData, $data)
                )->setFrom(
                    $senderEmail
                )->addTo(
                    $sendTo
                );

                if (
                    $this->helper->isPdfAttach()
                    && $configPath == Data::CONFIG_PATH_CUSTOMER_APPROVE_EMAIL
                ) {
                    $this->layout->getUpdate()->load('amasty_quote_quote_pdf');
                    $this->layout->generateXml();
                    $this->layout->generateElements();
                    $pdfText = $this->pdfProvider->generatePdfText();
                    $transport->addAttachment($pdfText, $data['quote']->getIncrementId());
                }
                $transport->getTransport()->sendMessage();

//                $sendToAdmin = $this->helper->getSendToEmail('admin_notifications');
//                if (!in_array($mailTemplateId, $this->sendQuoteEditEmailAdmin())) {
//                    $sendToAdmin = $this->helper->getSendToEmail('admin_notifications');
//                }
//                $email_template = $this->emailTemplate->load('new_admin_notification', 'template_code');
//                $templateid = $email_template->getId();
//
//                $transport1 = $this->transportBuilder->setTemplateIdentifier(
//                    $templateid
//                )->setTemplateModel(
//                    Template::class
//                )->setTemplateOptions(
//                    ['area' => Area::AREA_FRONTEND, 'store' => $store->getId()]
//                )->setTemplateVars(
//                    array_merge($defaultData, $data)
//                )->setFrom(
//                    $senderEmail
//                )->addTo($sendToAdmin);
//
//                $transport1->getTransport()->sendMessage();
            } catch (Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }

    /**
     * @return Session
     */
    private function getCustomerSession(): Session
    {
        return $this->customerSessionFactory->create();
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route, array $params = []): string
    {
        return $this->helper->getUrl($route, $params);
    }

    /**
     * @param Quote $quote
     * @param string $emailTemplate
     * @param int|null $notificationTemplateId
     * @return void
     * @throws Exception
     */
    private function sendQuoteEmail(Quote $quote, string $emailTemplate, int $notificationTemplateId = null): void
    {
        $this->storeEmulation->startEnvironmentEmulation($quote->getStoreId());
        $this->helper->clearScopeUrl();
        $formattedDate = date("M d, Y, h:i:s A", strtotime($quote->getSubmitedDate()));
        $timezoneInterface = $this->timezoneInterface;
        $currentDate = $timezoneInterface->date(new \DateTime($formattedDate));
        $formattedDate = $timezoneInterface->formatDateTime($currentDate, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT);
        $this->sendEmail(
            $emailTemplate,
            $quote->getCustomerEmail(),
            [
                'viewUrl' => $this->urlResolver->getViewUrl((int) $quote->getId(), ['_nosid' => true]),
                'quote' => $quote,
                'created_at' => $formattedDate,
                'customerName' => $quote->getCustomerName(),
                'store' => $quote->getStore(),
                'expiredDate' => $this->getExpiredDate($quote),
                'remarks' => $this->retrieveCustomerNote($quote->getRemarks()),
                'adminRemarks' => $this->retrieveAdminNote($quote->getRemarks())
            ],
            $notificationTemplateId
        );
        $this->storeEmulation->stopEnvironmentEmulation();
        $this->helper->clearScopeUrl();

    }

    /**
     * @param Quote $quote
     *
     * @return $this
     * @throws Exception
     */
    public function sendQuoteEditEmail(Quote $quote): Sender
    {
        if ($quote->getAllItems()) {
            $this->sendQuoteEmail(
                $quote,
                Data::CONFIG_PATH_CUSTOMER_EDIT_EMAIL,
                CustomerNotificationTemplates::MODIFIED_QUOTE
            );
        }

        return $this;
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     * @throws Exception
     */
    public function sendNewQuoteEmail(Quote $quote): Sender
    {
        $this->sendQuoteEmail($quote, Data::CONFIG_PATH_CUSTOMER_NEW_EMAIL);

        return $this;
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     * @throws Exception
     */
    public function sendApproveEmail(Quote $quote): Sender
    {
        $this->sendQuoteEmail(
            $quote,
            Data::CONFIG_PATH_CUSTOMER_APPROVE_EMAIL,
            CustomerNotificationTemplates::APPROVED_QUOTE
        );

        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     * @throws Exception
     */
    public function sendDeclineEmail(Quote $quote): Sender
    {
        $this->sendQuoteEmail(
            $quote,
            Data::CONFIG_PATH_CUSTOMER_CANCEL_EMAIL,
            CustomerNotificationTemplates::CANCELED_QUOTE
        );

        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     * @throws Exception
     */
    public function sendExpiredEmail(Quote $quote): Sender
    {
        $this->sendQuoteEmail(
            $quote,
            Data::CONFIG_PATH_CUSTOMER_EXPIRED_EMAIL,
            CustomerNotificationTemplates::EXPIRED_QUOTE
        );

        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     * @throws Exception
     */
    public function sendReminderEmail(Quote $quote): Sender
    {
        $this->registry->register('amasty_quote_currency', $quote->getQuoteCurrencyCode());
        $this->sendQuoteEmail(
            $quote,
            Data::CONFIG_PATH_CUSTOMER_REMINDER_EMAIL,
            CustomerNotificationTemplates::REMINDER
        );
        $this->registry->unregister('amasty_quote_currency');

        return $this;
    }

    /**
     * @param Quote $quote
     * @return string|null
     */
    private function getExpiredDate(Quote $quote): ?string
    {
        $result = null;

        if ($this->helper->getExpirationTime() !== null && $quote->getExpiredDate()) {
            $result = $this->dateHelper->formatDate($quote->getExpiredDate());
        }

        return $result;
    }

    /**
     * @param string|null $remarks
     * @return string
     */
    private function retrieveCustomerNote(?string $remarks): string
    {
        $additionalData = $this->serializer->unserialize($remarks);

        return $additionalData[QuoteInterface::CUSTOMER_NOTE_KEY] ?? '';
    }

    /**
     * @param string|null $remarks
     * @return string
     */
    private function retrieveAdminNote(?string $remarks): string
    {
        $additionalData = $this->serializer->unserialize($remarks);

        return $additionalData[QuoteInterface::ADMIN_NOTE_KEY] ?? '';
    }

    /**
     * @return false|string[]
     */
    public function sendQuoteEditEmailAdmin()
    {
        $scopeConfig = $this->scopeConfig;
        $scopeConfigValue = $scopeConfig->getValue('amasty_request_quote/admin_notifications/disable_notifications_for');
        return explode(',', $scopeConfigValue);
    }

}
