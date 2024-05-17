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

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;

/**
 * Class MassAttributeSet
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassAttributeSet extends AbstractMassAction
{
    /**
     * @param AbstractCollection $collection
     *
     * @return $this|mixed
     */
    public function massAction($collection)
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data    = $request->getPost('product');

        /** @var Result $resultBlock */
        $resultBlock = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');

        foreach ($collection->getItems() as $product) {
            /** @var Product $product */
            if (empty($data['attribute_set_id'])) {
                $this->_productNonUpdated++;
                continue;
            }
            try {
                $product->setAttributeSetId($data['attribute_set_id']);
                $this->_productResource->save($product);
                $this->_productUpdated++;
            } catch (Exception $e) {
                $resultBlock->addError(__($e->getMessage()));
                $this->_productNonUpdated++;
            }
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'attribute set'));
    }
}
