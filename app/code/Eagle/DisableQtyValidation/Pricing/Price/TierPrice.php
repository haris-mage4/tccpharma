<?php

namespace Eagle\DisableQtyValidation\Pricing\Price;

use Magento\Catalog\Pricing\Price\TierPrice as MagentoTierPrice;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\LocalizedException;

class TierPrice extends MagentoTierPrice
{
    /**
     * @param array $priceList
     * @return array
     * @throws LocalizedException
     */
    protected function filterTierPrices(array $priceList)
    {
        $qtyCache = [];
        $allCustomersGroupId = $this->groupManagement->getAllCustomersGroup()->getId();

        foreach ($priceList as $priceKey => &$price) {

            if ('0.01' == $this->priceInfo->getPrice(FinalPrice::PRICE_CODE)->getValue()) {
                unset($priceList[$priceKey]);
                continue;
            }

            if (isset($price['price_qty']) && $price['price_qty'] == 1) {
                unset($priceList[$priceKey]);
                continue;
            }

            /* filter price by customer group */
            if (
                $price['cust_group'] != $this->customerGroup &&
                $price['cust_group'] != $allCustomersGroupId
            ) {
                unset($priceList[$priceKey]);
                continue;
            }

            /* select a lower price for each quantity */
            if (isset($qtyCache[$price['price_qty']])) {
                $priceQty = $qtyCache[$price['price_qty']];
                if ($this->isFirstPriceBetter($price['website_price'], $priceList[$priceQty]['website_price'])) {
                    unset($priceList[$priceQty]);
                    $qtyCache[$price['price_qty']] = $priceKey;
                } else {
                    unset($priceList[$priceKey]);
                }
            } else {
                $qtyCache[$price['price_qty']] = $priceKey;
            }
        }

        return array_values($priceList);
    }
}
