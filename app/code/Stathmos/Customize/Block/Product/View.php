<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Stathmos\Customize\Block\Product;

/**
 * Class AbstractProduct
 * @api
 * @deprecated 102.0.0
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class View extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {        
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getProduct()
    {
    	$product = $this->_registry->registry('current_product');
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($product->getId());
		// echo "<pre>";
		// print_r($product->getData());
		// die;
        return $product;
    }
}
