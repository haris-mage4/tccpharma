<?php
/**
 * TCCPharma CatalogUrlRewrite plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package    TCCPharma_CatalogUrlRewrite
 * @category   TCCPharma
 * @copyright  Copyright (c) 2023 TCCPharma
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace TCCPharma\CatalogUrlRewrite\Plugin\Model;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;

/**
 * Class ProductUrlPathGeneratorPlugin
 * @package TCCPharma\CatalogUrlRewrite\Plugin\Model
 */
class ProductUrlPathGeneratorPlugin
{
    /**
     * Generate product url key based on url_key entered by merchant or product name
     *
     * @param Product $product
     * @return string|null
     */
    public function aroundGetUrlKey(
        ProductUrlPathGenerator $subject,
        \Closure                $proceed,
                                $product
    )
    {
        $generatedProductUrlKey = $this->prepareProductUrlKey($product);
        var_dump($generatedProductUrlKey);die;
        return (
            $product->getUrlKey() === false ||
            empty($generatedProductUrlKey)
        )
            ? null
            : $generatedProductUrlKey;
    }
    
    /**
     * Prepare url key for product
     *
     * @param Product $product
     * @return string
     */
    protected function prepareProductUrlKey(Product $product)
    {
        $urlKey = (string)$product->getUrlKey();
        $urlKey = trim(strtolower($urlKey));
        $urlKey = ($urlKey && $product->getOrigData('sku') === $product->getSku()) ? $urlKey : $product->getSku();
        
        return $product->formatUrlKey($urlKey);
    }
}