<?php
/**
 * Copyright © Magemonkeys All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magemonkeys\Quote\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class Data extends AbstractHelper
{
    protected $productFactory;
    
    protected $priceHelper;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        ProductFactory $productFactory,
        PriceHelper $priceHelper,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->productFactory = $productFactory;
        $this->priceHelper = $priceHelper;
        parent::__construct($context);
    }

    /**
     * Get NDC from product attribute
     *
     * @param int $sku
     * @return string
     */
    public function getProductNdc($sku) {       
        $product = $this->productFactory->create();
        $ndc = $product->loadByAttribute('sku', $sku)->getData('ndc');        
        return $ndc; 
    }

    public function getProductSize($sku) {       
        $product = $this->productFactory->create();
        $ndc = $product->loadByAttribute('sku', $sku)->getData('size');        
        return $ndc; 
    }

    public function getProductPrice($sku) {       
        $product = $this->productFactory->create();
        $price = $product->loadByAttribute('sku', $sku)->getData('price');
        $formatedPrice = $this->priceHelper->currency($price, true, false);        
        return $formatedPrice; 
    }
}

