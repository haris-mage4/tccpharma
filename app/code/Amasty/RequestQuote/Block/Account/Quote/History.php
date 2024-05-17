<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Account\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\ResourceModel\Quote;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\Template;
use Amasty\RequestQuote\Model\Source\Status;
use Amasty\RequestQuote\Api\FiltersInterface;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_RequestQuote::account/quote/history.phtml';

    /**
     * @var Quote\Account\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Quote\Collection|null
     */
    private $quotes = null;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var Status
     */
    private $statusConfig;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    private $configHelper;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var FiltersInterface[]
     */
    private $specialFilter;

    /**
     * @param FiltersInterface[] $specialFilter
     * @param array $data
     */

    public function __construct(
        Quote\Account\CollectionFactory $collectionFactory,
        Status $statusConfig,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        SessionFactory $sessionFactory,
        Template\Context $context,
        PostHelper $postHelper,
        UrlResolver $urlResolver,
        array                             $specialFilter = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->sessionFactory = $sessionFactory;
        $this->statusConfig = $statusConfig;
        $this->configHelper = $configHelper;
        $this->postHelper = $postHelper;
        $this->urlResolver = $urlResolver;
        $this->specialFilter = $specialFilter;
        $this->validate();
    }

    /**
     * @return Quote\Collection|null
     */
    public function getQuotes()
    {
        if (
            $this->quotes === null
            && ($customerId = $this->getCustomerSession()->getCustomerId())
        ) {
            $this->quotes = $this->collectionFactory->create($customerId)
                ->addFieldToFilter('amasty_quote.status', [
                    'in' => $this->statusConfig->getVisibleOnFrontStatuses()
                ])
                ->setOrder('created_at', 'desc');

            $post = $this->getRequest()->getParams();
            if (isset($post)) {
                if (!empty($post['quote_id'])) {
                    $this->quotes->addFieldToFilter(
                        'increment_id',
                        array('like' => '%' . $post['quote_id'] . '%')
                    );
                }
            }

            if (isset($post)) {
                if (!empty($post['grand_total'])) {
                    $this->quotes->addFieldToFilter(
                        'grand_total',
                        $post['grand_total']
                    );
                }
            }

            if (
                isset($post['status']) && ($post['status'] == 1 || $post['status'] == 0)
                && ($post['status'] === 1 || $post['status'] === 0)  && ($post['status'] === 2 || $post['status'] === 0)
                && ($post['status'] === 3 || $post['status'] === 0)
            ) {

                $quoteIdsToRemove = [];
                foreach ($this->quotes as $quote) {
                    $quoteItems = $quote->getAllVisibleItems();
                    $hasApprovedItem = false;
                    foreach ($quoteItems as $quoteItem) {
                        if ($quoteItem->getApprovalStatus() == 1) {
                            $hasApprovedItem = true;
                            break;
                        }
                        if ($quoteItem->getApprovalStatus() == 2) {
                            $hasApprovedItem = true;
                            break;
                        }
                        if ($quoteItem->getApprovalStatus() == 3) {
                            $hasApprovedItem = true;
                            break;
                        }
                    }
                    if (!$hasApprovedItem) {
                        $quoteIdsToRemove[] = $quote->getId();
                    }
                }
                $this->quotes->addFieldToFilter('entity_id', ['nin' => $quoteIdsToRemove]);
            }

            $this->quotes->addFieldToSelect('*');

            foreach ($this->specialFilter as $filters) {
                if ($filters->isFilterable($post)) {
                    $this->quotes = $filters->filter($this->quotes, $post);
                }
            }

            /*Quote Filters*/
        }

        return $this->quotes;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuotes()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'amasty.quote.history.pager'
            )->setCollection(
                $this->getQuotes()
            );
            $this->setChild('pager', $pager);
            $this->getQuotes()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function getViewUrl($quote)
    {
        return $this->urlResolver->getViewUrl((int) $quote->getId());
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function getDeleteUrl($quote)
    {
        return $this->getUrl('amasty_quote/account/delete', ['quote_id' => $quote->getId()]);
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return string
     */
    public function getMoveUrl($quote)
    {
        return $this->_urlBuilder->getUrl('amasty_quote/move/inCart', ['quote_id' => $quote->getId()]);
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return bool
     */
    public function isMoveShowed($quote)
    {
        return $quote->getStatus() == Status::APPROVED;
    }

    /**
     * @return bool
     */
    public function isExpiryColumnShow()
    {
        return $this->configHelper->getExpirationTime() !== null;
    }

    public function getExpiredDate(QuoteInterface $quote): string
    {
        $result = __('N/A')->render();
        if (
            $quote->getExpiredDate()
            && in_array($quote->getStatus(), [Status::APPROVED, Status::EXPIRED])
        ) {
            $result = (string) $this->formatDate($quote->getExpiredDate());
        }

        return $result;
    }

    public function getPostData(string $url): string
    {
        return $this->postHelper->getPostData($url);
    }

    /*Quote Filters*/

    /**
     * @return void
     */
    private function validate(): void
    {
        foreach ($this->specialFilter as $specialFilter) {
            if (!$specialFilter instanceof FiltersInterface) {
                throw new InvalidArgumentException('Invalid object type.');
            }
        }
    }

    /*Quote Filters*/
}
