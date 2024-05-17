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

namespace Mageplaza\MassProductActions\Controller\Adminhtml\Product;

use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class MassRelated
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassRelated extends AbstractLinkProduct
{
    /**
     * @param Collection $collection
     *
     * @return $this|mixed
     */
    public function massAction($collection)
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data    = $request->getPost('related');

        return $this->updateLinkProduct($data, $collection);
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function getCurrentLinkProductSku($product)
    {
        $currentSku = [];
        /** @var AbstractCollection $currentLinkProdCol */
        if ($currentLinkProdCol = $product->getRelatedProductCollection()) {
            foreach ($currentLinkProdCol as $currentLinkProd) {
                /** @var Product $currentLinkProd */
                $currentSku[] = $currentLinkProd->getSku();
            }
        }

        return $currentSku;
    }

    /**
     * @param Product $product
     * @param Product $linkedProduct
     * @param int $position
     *
     * @return ProductLinkInterface
     */
    public function generateProductLink($product, $linkedProduct, $position)
    {
        $linkData = $this->_productLinkFact->create();
        $linkData
            ->setSku($product->getSku())
            ->setLinkedProductSku($linkedProduct->getSku())
            ->setLinkType('related')
            ->setPosition($position);

        return $linkData;
    }

    /**
     * @param array $newSkuLinks
     * @param Product $product
     *
     * @return array
     */
    public function getProductLinkData($newSkuLinks, $product)
    {
        $linkDataAll = [];
        if ($oldLinks = $product->getProductLinks()) {
            foreach ($oldLinks as $oldLink) {
                if ($oldLink->getLinkType() !== 'related') {
                    $linkDataAll[] = $oldLink;
                }
            }
        }
        foreach ($newSkuLinks as $key => $newSkuLink) {
            /** @var Product $linkedProduct */
            $linkedProduct = $this->_getProductBySku($newSkuLink);
            if ($linkedProduct && $newSkuLink !== $product->getSku()) {
                $linkDataAll[] = $this->generateProductLink($product, $linkedProduct, $key);
            }
        }

        return $linkDataAll;
    }
}
