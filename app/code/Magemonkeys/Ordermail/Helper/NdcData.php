<?php
/**
 * Copyright © Magemonkeys All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magemonkeys\Ordermail\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ProductFactory;

class NdcData extends AbstractHelper
{
    protected $productFactory;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        ProductFactory $productFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->productFactory = $productFactory;
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

}

