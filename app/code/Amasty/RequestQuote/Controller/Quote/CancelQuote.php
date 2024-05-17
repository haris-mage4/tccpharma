<?php
/**
 * @author Kailash Mishra
 */
namespace Amasty\RequestQuote\Controller\Quote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * CancelQuote
 */
class CancelQuote extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonResultFactory;
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;
    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;

    /**
     * @var QuoteRepositoryInterface
     */
    private QuoteRepositoryInterface $quoteRepository;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;


    /**
     * @param Context $context
     * @param JsonFactory $jsonResultFactory
     * @param ResourceConnection $resourceConnection
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        ResourceConnection $resourceConnection,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        QuoteRepositoryInterface $quoteRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
        $this->resourceConnection = $resourceConnection;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $itemId = $this->getRequest()->getParam('item_id');

        try {
            if ($itemId) {
                $currentDateTime = date('Y-m-d H:i:s');
                $connection = $this->resourceConnection->getConnection();
                $quoteItemTable = $connection->getTableName('quote_item');
                $data = [
                    'approval_status' => 2,
                    'cancelled_date' => $currentDateTime,
                ];

                $where = ['item_id = ?' => $itemId];
                $connection->update($quoteItemTable, $data, $where);

                $this->sendAdminEmail();

                $response = ['message' => 'Quote item canceled successfully'];
            } else {
                $response = ['message' => 'Invalid item ID'];
            }
        } catch (\Exception $e) {
            $response = ['message' => 'Error: ' . $e->getMessage()];
        }

        $result->setData($response);

        return $result;
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    private function sendAdminEmail()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = $this->quoteRepository->get($quoteId);
        $supportEmail = $this->scopeConfig->getValue('trans_email/ident_support/email');
        $adminEmail = $this->scopeConfig->getValue('trans_email/ident_general/email');

        if ($quote->getAllItems()) {
            $store = $this->storeManager->getStore();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('notifications_to_admin_for_item_cancel_template')
                ->setTemplateOptions(['area' => 'frontend', 'store' => $store->getId()])
                ->setTemplateVars(['quote' => $quote, 'quoteid' => $quoteId])
                ->setFrom(['email' => $adminEmail, 'name' => 'Admin'])
                ->addTo($supportEmail)
                ->getTransport();
            $transport->sendMessage();
        }
    }
}
