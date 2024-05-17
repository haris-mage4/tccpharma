<?php

/**
 * Copyright © Eagle All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Eagle\Tooltip\Helper\Config;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;

class TooltipConfig extends AbstractHelper
{
    protected const REGULAR_PRICE_TOOLTIP_TEXT = 'tooltip/general/regular_price_tooltip';
    protected const SPECIAL_PRICE_TOOLTIP_TEXT = 'tooltip/general/special_price_tooltip';
    protected CustomerSession $customerSession;
    protected ProductFactory $productFactory;
    protected ProductRepositoryInterface $productRepository;
    protected PricingHelper $pricingHelper;
    /**
     * @var ScopedProductTierPriceManagementInterface
     */
    private ScopedProductTierPriceManagementInterface $tierPrice;
    protected $scopeConfig;
    public function __construct(
        CustomerSession $customerSession,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        PricingHelper $pricingHelper,
        ScopeConfigInterface $scopeConfig,
        ScopedProductTierPriceManagementInterface $tierPrice
    ) {
        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->pricingHelper = $pricingHelper;
        $this->scopeConfig = $scopeConfig;
        $this->tierPrice = $tierPrice;
    }

    /**
     * @return string
     */

    public function getRegularPriceText(): string
    {
        return $this->scopeConfig->getValue(self::REGULAR_PRICE_TOOLTIP_TEXT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getSpecialPriceText(): string
    {
        return $this->scopeConfig->getValue(self::SPECIAL_PRICE_TOOLTIP_TEXT, ScopeInterface::SCOPE_STORE);
    }
    /**
     *
     * Check if the product has advanced price
     * @param $product
     * @return bool
     */
    public function hasAdvancedPrice($product): bool
    {
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $tierPrices = $product->getTierPrices();
        foreach ($tierPrices as $tierPrice) {
            if ($tierPrice->getCustomerGroupId() === $customerGroupId) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * Check if the product has advanced price
     * @param $product
     * @return bool
     */
    public function hasAdvancedPriceForGroup($product): bool
    {
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $tierPrices = $product->getTierPrices();
        foreach ($tierPrices as $tierPrice) {
            if ($tierPrice->getCustomerGroupId() !== 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the lowest tier price for a specific customer group based on the lowest quantity
     * @param string $productSku
     * @return float|null
     */
    public function getLowestTierPriceForCustomerGroup(string $productSku)
    {
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $product = $this->productRepository->get($productSku);
        $tierPrices = $product->getTierPrices();
        $lowestPrice = null;
        $lowestQty = null;

        foreach ($tierPrices as $tierPrice) {
            $price = $tierPrice->getValue();
            $customerGroupIds = $tierPrice->getCustomerGroupId();
            $qty = $tierPrice->getQty();

            // Check if the customer group ID matches the desired customer group
            if ($customerGroupIds == $customerGroupId) {
                if ($lowestQty === null || $qty < $lowestQty) {
                    $lowestQty = $qty;
                    $lowestPrice = $price;
                } elseif ($qty == $lowestQty && ($lowestPrice === null || $price < $lowestPrice)) {
                    $lowestPrice = $price;
                }
            }
        }

        return $lowestPrice;
    }


    /**
     *Format price according to store's currency settings
     *
     * @param float|null $price
     * @return string
     */

    public function formatPrice(?float $price): string
    {
        if ($price === null) {
            return '';
        }
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Get tier price by lowest quantity for the customer group
     *
     * @param string $sku
     * @param int $customerGroupId
     * @return float|null
     */
    public function tierPriceByCustomerId($sku, $customerGroupId)
    {
        $tierPrices = $this->tierPrice->getList($sku, $customerGroupId);

        $lowestPrice = null;
        $lowestQty = null;

        foreach ($tierPrices as $tierPrice) {
            if ($tierPrice->getCustomerGroupId() === $customerGroupId) {
                $qty = $tierPrice->getQty();
                $price = $tierPrice->getValue();

                if ($lowestQty === null || $qty < $lowestQty) {
                    $lowestQty = $qty;
                    $lowestPrice = $price;
                }
            }
        }

        return $lowestPrice !== null ? number_format((float)$lowestPrice, 2, '.', '') : null;
    }

    /**
     * Get special price quantity for the customer group based on the lowest quantity
     *
     * @param string $sku
     * @param int $customerGroupId
     * @return int|null
     */
    public function getSpecialPriceQtyByCustomerGroup(string $sku, int $customerGroupId)
    {
        $tierPrices = $this->tierPrice->getList($sku, $customerGroupId);

        $lowestQty = null;

        foreach ($tierPrices as $tierPrice) {
            if ($tierPrice->getCustomerGroupId() === $customerGroupId) {
                $qty = $tierPrice->getQty();

                if ($lowestQty === null || $qty < $lowestQty) {
                    $lowestQty = $qty;
                }
            }
        }
        return $lowestQty;
    }

    /**
     * Get tier price based on quantity
     *
     * @param string $sku Product SKU
     * @param int $customerGroupId Customer group ID
     * @param int|float $qty Quantity
     * @return string|null Tier price for the given quantity, or null if no matching tier price is found
     * @throws NoSuchEntityException
     */
    public function getTierPriceByQty(string $sku, int $customerGroupId, $qty)
    {
        $tierPriceData = $this->tierPrice->getList($sku, $customerGroupId);

        $tierPrice = null;

        foreach ($tierPriceData as $tier) {
            if ($qty >= $tier['qty']) {
                $tierPrice = number_format((float)$tier['value'], 2, '.', '');;
            } else {
                break;
            }
        }

        return $tierPrice;
    }
}
