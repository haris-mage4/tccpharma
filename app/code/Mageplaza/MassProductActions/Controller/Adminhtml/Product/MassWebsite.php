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
use Magento\Framework\App\Request\Http;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;

/**
 * Class MassWebsite
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassWebsite extends AbstractMassAction
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
        $resultBlock       = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');
        $websiteRemoveData = isset($data['remove_website_ids']) ? $data['remove_website_ids'] : [];
        $websiteAddData    = isset($data['add_website_ids']) ? $data['add_website_ids'] : [];

        try {
            if ($websiteAddData || $websiteRemoveData) {
                if ($websiteRemoveData) {
                    $this->_productAction->updateWebsites($collection->getAllIds(), $websiteRemoveData, 'remove');
                }
                if ($websiteAddData) {
                    $this->_productAction->updateWebsites($collection->getAllIds(), $websiteAddData, 'add');
                }
                $this->_productUpdated = count($collection->getAllIds());
                $this->_flatProcessor->reindexList($collection->getAllIds());
                if (!empty($websiteRemoveData) || !empty($websiteAddData)) {
                    $this->_priceProcessor->reindexList($collection->getAllIds());
                }
            }
        } catch (Exception $e) {
            $resultBlock->addError(__($e->getMessage()));
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'websites'));
    }
}
