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

namespace Mageplaza\MassProductActions\Helper;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryColFact;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\MassProductActions\Model\Config\Source\System\Actions;

/**
 * Class Data
 * @package Mageplaza\MassProductActions\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpmassproductactions';

    /**
     * @var CategoryColFact
     */
    protected $_categoryColFact;

    /**
     * @var Actions
     */
    protected $_massActions;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CategoryColFact $categoryColFact
     * @param Actions $massAction
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CategoryColFact $categoryColFact,
        Actions $massAction
    ) {
        $this->_categoryColFact = $categoryColFact;
        $this->_massActions     = $massAction;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getCategoriesTree()
    {
        /* @var Collection $collection */
        $collection = $this->_categoryColFact->create()->setStoreId(0);

        $collection->addAttributeToSelect(['name', 'is_active', 'parent_id']);

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value'    => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }

            $categoryById[$category->getId()]['is_active']        = 1;
            $categoryById[$category->getId()]['label']            = $category->getName();
            $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
        }

        return self::jsonEncode($categoryById[CategoryModel::TREE_ROOT_ID]['optgroup']);
    }

    /**
     * @return WebsiteInterface[]
     */
    public function getWebsiteCollection()
    {
        return $this->storeManager->getWebsites();
    }

    /**
     * @param Website $website
     *
     * @return Store[]
     */
    public function getGroupCollection($website)
    {
        return $website->getGroups();
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    public function getStoreCollection($group)
    {
        return $group->getStores();
    }

    /**
     * @param string $fileUrl
     *
     * @return string
     */
    public function getSubmitProductHtml($fileUrl)
    {
        return '<button type="button" class="mp_submit_products_grid" style="display: none;" onclick="mpMassProductAction.submitProductsGrid(event);this.hide();">
                        <span>' . __('Submit') . '</span>
              </button>
              <div class="mpmassproductactions_image_loader">
                    <div class="loader">
                            <img src="' . $fileUrl . '"
                                 alt="' . __('Loading') . '">
                    </div>
              </div>';
    }

    /**
     * @param string $onclickText
     *
     * @return string
     */
    public function getSelectProductHtml($onclickText)
    {
        return '<button type="button" class="mp_load_products_grid"
                        onclick="' . $onclickText . '">
                    <span>' . __('Select') . '</span>
                </button>';
    }

    /**
     * @return array
     */
    public function getActionsConfig()
    {
        $selectedActions = [];
        $actionPositions = [];
        $massActions     = $this->_massActions->toOptionArray();
        $actionConfig    = self::jsonDecode($this->getConfigGeneral('actions'));
        foreach ($massActions as $key => $action) {
            $selectedActions[$action['type']] = '1';
            $actionPositions[$action['type']] = $key;
        }

        if ($actionConfig) {
            $selectedActions = [];
            if (isset($actionConfig['selected'])) {
                $selectedActions = $actionConfig['selected'];
            }
            $actionPositions = $actionConfig['position'];
        }

        $result = [
            'selected_actions' => $selectedActions,
            'action_positions' => $actionPositions
        ];

        return $result;
    }

    /**
     * Get multi sources
     *
     * @return mixed
     */
    public function getSourceList()
    {
        return $this->createObject(\Magento\Inventory\Model\ResourceModel\Source\Collection::class)->load();
    }
}
