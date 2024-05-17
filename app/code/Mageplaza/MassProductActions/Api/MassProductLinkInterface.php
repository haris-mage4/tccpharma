<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_MassProductActions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\MassProductActions\Api;

/**
 * Interface MassProductLinkInterface
 * @package Mageplaza\MassProductActions\Api
 */
interface MassProductLinkInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getCurrentLinkProductSku($product);

    /**
     * @param array $newSkuLinks
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getProductLinkData($newSkuLinks, $product);

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $linkedProduct
     * @param int $position
     *
     * @return mixed
     */
    public function generateProductLink($product, $linkedProduct, $position);
}
