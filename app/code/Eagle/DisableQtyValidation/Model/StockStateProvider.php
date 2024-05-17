<?php
namespace Eagle\DisableQtyValidation\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockStateProvider as MagentoStockStateProvider;
use Magento\Framework\DataObject;

class StockStateProvider extends MagentoStockStateProvider
{
    /**
     * Disable the out-of-stock validation in checkQuoteItemQty.
     *
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @param int|float $summaryQty
     * @param int|float $origQty
     * @return DataObject
     */
    public function checkQuoteItemQty(
        StockItemInterface $stockItem,
        $qty,
        $summaryQty,
        $origQty = 0
    ): DataObject
    {
        $result = parent::checkQuoteItemQty($stockItem, $qty, $summaryQty, $origQty);

        if (!$stockItem->getIsInStock() && $result->getHasError()) {
            $result->setHasError(false)
                ->setErrorCode('')
                ->setMessage(null)
                ->setQuoteMessage('')
                ->setQuoteMessageIndex('');
        }

        return $result;
    }
}
