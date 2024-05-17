<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Model\Quote\Item;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Laminas\Code\Exception\InvalidArgumentException;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\App\ResourceConnection;
use Amasty\RequestQuote\Model\Source\Status;

class Updater
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Amasty\RequestQuote\Helper\Date
     */
    protected $dateHelper;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    protected $configHelper;

    /**
     * @var \Amasty\RequestQuote\Model\Email\Sender
     */
    protected $emailSender;

    protected $resourceConnection;

    protected $request;

    public function __construct(
        ProductFactory $productFactory,
        FormatInterface $localeFormat,
        ObjectFactory $objectFactory,
        \Amasty\Base\Model\Serializer $serializer,
        PriceCurrencyInterface $priceCurrency,
        \Amasty\RequestQuote\Model\Email\Sender $emailSender,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        \Amasty\RequestQuote\Helper\Date $dateHelper,
        ResourceConnection $resourceConnection,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->productFactory = $productFactory;
        $this->localeFormat = $localeFormat;
        $this->objectFactory = $objectFactory;
        $this->priceCurrency = $priceCurrency;
        $this->serializer = $serializer;
        $this->emailSender = $emailSender;
        $this->configHelper = $configHelper;
        $this->dateHelper = $dateHelper;
        $this->resourceConnection = $resourceConnection;
        $this->request = $request;
    }

    /**
     * @param Item $item
     * @param array $info
     * @throws InvalidArgumentException
     * @return Updater
     */
    public function update(Item $item, array $info)
    {
        if (!isset($info['qty'])) {
            throw new InvalidArgumentException(__('The qty value is required to update quote item.'));
        }
        if (!isset($info['price'])) {
            $info['price'] = null;
        }
        $itemQty = $info['qty'];
        if ($item->getProduct()->getStockItem()) {
            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                $itemQty = (int)$info['qty'];
            } else {
                $item->setIsQtyDecimal(1);
            }
        }
        $itemQty = $itemQty > 0 ? $itemQty : 1;
        $availableQty = '';
        $quoteNote = '';
        $quoteItemApprovalStatus = '';
        $emailSubject = '';
        $requestedQty = '';
        $cancelledDate = null;
        $isCancelByAdmin = null;

        if (isset($info['approval_status'])) {
            if ($info['approval_status'] == 1) {
                $this->updateQuoteData();
            }
            if ($info['approval_status'] == 3) {
                $cancelledDate = date('Y-m-d H:i:s');
                $isCancelByAdmin = $info['approval_status'];
            }

            $quoteItemApprovalStatus = ($info['approval_status'] == 3) ? '2' : $info['approval_status'];
        }
        if (isset($info['email_subject'])) {
            $emailSubject = $info['email_subject'];
        }
        if (isset($info['available_qty'])) {
            $availableQty = $info['available_qty'];
        }
        if (isset($info['quote_note'])) {
            $quoteNote = $info['quote_note'];
        }
        if (isset($info['requested_qty'])) {
            $requestedQty = $info['requested_qty'];
        }

        $this->setPrice($info, $item);
        $this->setItemNote($info, $item);
        $this->setRequestedPrice($info, $item);
        if (empty($info['action']) || !empty($info['configured'])) {
            $item->setQty($requestedQty);
            $item->setRequestedQty($requestedQty);
            $item->setAvailableQty($availableQty);
            $item->setEmailSubject($emailSubject);
            $item->setQuoteNote($quoteNote);
            $item->setIsCancelByAdmin($isCancelByAdmin);
            $item->setApprovalStatus($quoteItemApprovalStatus);
            $item->setCancelledDate($cancelledDate);
            $item->setNoDiscount(true);
            $item->getProduct()->setIsSuperMode(true);
            $item->getProduct()->unsSkipCheckRequiredOption();
            $item->checkData();
        }

        return $this;
    }

    /**
     * @param array $info
     * @param Item $item
     * @return void
     */
    private function setPrice(array $info, Item $item)
    {
        if ($price = $info['price']) {
            $itemPrice = $this->parsePrice($price, $item);
            $itemPrice = $this->applyPriceModificators($itemPrice, $info['modificators']);
            /** @var \Magento\Framework\DataObject $infoBuyRequest */
            $infoBuyRequest = $item->getBuyRequest();
            if ($infoBuyRequest) {
                $infoBuyRequest->setValue($this->serializer->serialize($infoBuyRequest->getData()));
                $infoBuyRequest->setCode('info_buyRequest');
                $infoBuyRequest->setProduct($item->getProduct());

                $item->addOption($infoBuyRequest);
            }
            $item->setData('custom_price', $itemPrice);
            $item->setData('original_custom_price', $itemPrice);
        } else {
            $item->setData('custom_price', null);
            $item->setData('original_custom_price', null);
        }
    }

    /**
     * @param float $price
     * @param array $modificators
     * @return float|int
     */
    protected function applyPriceModificators($price, $modificators)
    {
        foreach ($modificators as $modificator => $percent) {
            if (!$percent || $percent > 100) {
                continue;
            }
            switch ($modificator) {
                case QuoteInterface::DISCOUNT:
                    $price = $price - ($price * $percent / 100);
                    break;
                case QuoteInterface::SURCHARGE:
                    $price = $price + ($price * $percent / 100);
                    break;
            }
            if ($percent) {
                break;
            }
        }
        $this->priceCurrency->round($price);

        return $price;
    }

    /**
     * @param float|int $price
     * @param Item $item
     * @return float|int
     */
    private function parsePrice($price, Item $item)
    {
        $price = $this->localeFormat->getNumber($price);

        $quote = $item->getQuote();
        if ($quote->getQuoteCurrencyCode() && $quote->getQuoteCurrencyCode() != $quote->getBaseCurrencyCode()) {
            $rate = $quote->getStore()->getBaseCurrency()->getRate(
                $this->priceCurrency->getCurrency(null, $quote->getQuoteCurrencyCode())
            );
            if ($rate != 1) {
                $price = (float)$price / (float)$rate;
            }
        }

        return $price > 0 ? $price : 0;
    }

    /**
     * @param array $info
     * @param Item $item
     * @return $this
     */
    private function setItemNote(array $info, Item $item)
    {
        if (isset($info['note'])) {
            $itemNote = $this->serializer->unserialize($item->getAdditionalData()) ?: [];
            $itemNote['admin_note'] = trim($info['note']);
            $item->setAdditionalData($this->serializer->serialize($itemNote));
        }
        return $this;
    }

    /**
     * @param array $info
     * @param Item $item
     * @return $this
     */
    private function setRequestedPrice(array $info, Item $item)
    {
        if (isset($info['requested_price'])) {
            $itemNote = $this->serializer->unserialize($item->getAdditionalData()) ?: [];
            $itemNote['requested_price'] = trim($info['requested_price']);
            $itemNote['requested_custom_price'] = trim($info['requested_price']);
            $item->setAdditionalData($this->serializer->serialize($itemNote));
        }
        return $this;
    }

    /**
     * Update quote data in the 'amasty_quote' table, setting approval status to 'approved'
     * and updating the 'expired_date' and 'reminder_date' based on configuration settings.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateQuoteData()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('amasty_quote');
        $quoteId = $this->request->getParam('quote_id');
        $newExpiredDate = null;
        $newReminderDate = null;

        if ($expDays = $this->configHelper->getExpirationTime()) {
            $newExpiredDate = $this->dateHelper->increaseDays($expDays);
        }
        if ($remDays = $this->configHelper->getReminderTime()) {
            $newReminderDate = $this->dateHelper->increaseDays($remDays);
        }

        $data = [
            'status' => Status::APPROVED,
            'expired_date' => $newExpiredDate,
            'reminder_date' => $newReminderDate,
        ];
        $where = ['quote_id = ?' => $quoteId];

        $connection->update($tableName, $data, $where);
    }
}
