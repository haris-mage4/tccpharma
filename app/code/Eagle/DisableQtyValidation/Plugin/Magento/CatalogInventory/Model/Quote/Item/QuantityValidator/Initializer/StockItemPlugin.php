<?php

namespace Eagle\DisableQtyValidation\Plugin\Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem;
use Magento\Quote\Model\Quote\Item;

class StockItemPlugin
{
    /**
     * @param StockItem $subject
     * @param $result
     * @param StockItemInterface $stockItem
     * @param Item $quoteItem
     * @param $qty
     * @return mixed
     */
    public function afterInitialize(
        StockItem          $subject,
                           $result,
        StockItemInterface $stockItem,
        Item               $quoteItem,
                           $qty
    ) {
        if ($quoteItem->getQuote() instanceof \Amasty\RequestQuote\Model\Quote) {
            $result->setItemUseOldQty(false);
            $quoteItem->setUseOldQty($result->getItemUseOldQty());
        }

        return $result;
    }
}
