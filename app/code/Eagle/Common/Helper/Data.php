<?php
/**
 * Copyright © KailashMishra All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Eagle\Common\Helper;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class Data extends AbstractHelper
{
    /**
     * @var SessionFactory
     */
    protected  $sessionFactory;

    /**
     * @var UrlInterface
     */
    protected  $urlInterface;

    /**
     * @var ProductRepositoryInterface
     */
    protected  $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected  $stockRegistry;

    /**
     * @var PriceCurrencyInterface
     */
    protected  $priceCurrency;

    /**
     * @var PriceHelper
     */
    protected  $priceHelper;

    /**
     * @param Context $context
     * @param SessionFactory $sessionFactory
     * @param UrlInterface $urlInterface
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param PriceCurrencyInterface $priceCurrency
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        Context $context,
        SessionFactory $sessionFactory,
        UrlInterface $urlInterface,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        PriceCurrencyInterface $priceCurrency,
        PriceHelper $priceHelper
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->urlInterface = $urlInterface;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->priceCurrency = $priceCurrency;
        $this->priceHelper = $priceHelper;
        parent::__construct($context);
    }

    /**
     * @return Session
     */
    public function getCustomerSession(): Session
    {
        return $this->sessionFactory->create();
    }

    /**
     * Get current URL
     *
     * @return string
     */
    public function getCurrentUrl(): string
    {
        return $this->urlInterface->getCurrentUrl();
    }

    /**
     * Get product quantity by product ID
     *
     * @param int $productId
     * @return float|null
     */
    public function getProductQuantity(int $productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            return $stockItem->getQty();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format price
     */
    public function formatPrice($price): string
    {
        return $this->priceCurrency->format($price);
    }

    /**
     * @return PriceHelper
     */
    public function priceHelper(): PriceHelper
    {
        return $this->priceHelper;
    }
}
