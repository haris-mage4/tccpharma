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

namespace Mageplaza\MassProductActions\Block\Adminhtml\UpSell\Edit\Renderer;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Products
 * @package Mageplaza\MassProductActions\Block\Adminhtml\UpSell\Edit\Renderer
 */
class Products extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $_productColFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Products constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $productColFactory
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $productColFactory,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->_productColFactory = $productColFactory;
        $this->productRepository  = $productRepository;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @throws FileSystemException
     */
    protected function _construct()
    {
        $this->setId('upSellProductsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);

        parent::_construct();
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productColFactory->create()->addStoreFilter()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price');

        $productIds = $this->_getSelectedProducts();
        $collection->addFieldToFilter('entity_id', ['nin' => $productIds]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', [
            'header_css_class' => 'a-center',
            'type'             => 'checkbox',
            'name'             => 'in_product',
            'values'           => $this->_getSelectedProducts(),
            'align'            => 'center',
            'index'            => 'entity_id'
        ]);

        $this->addColumn('entity_id', [
            'header'           => __('Product ID'),
            'type'             => 'number',
            'index'            => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'index'  => 'name',
            'width'  => '50px',
        ]);

        $this->addColumn('sku', [
            'header' => __('Sku'),
            'index'  => 'sku',
            'width'  => '50px',
        ]);

        $this->addColumn('price', [
            'header' => __('Price'),
            'type'   => 'currency',
            'index'  => 'price',
            'width'  => '50px',
        ]);

        $this->addColumn('position', [
            'header'           => __('Position'),
            'name'             => 'position',
            'header_css_class' => 'hidden',
            'column_css_class' => 'hidden',
            'validate_class'   => 'validate-number',
            'index'            => 'position',
            'editable'         => true
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $ids = $this->getRequest()->getPost('up_sell_products', null);

        if (!$ids && $productSku = explode(',', $this->getRequest()->getParam('product_sku', null))) {
            foreach ($productSku as $sku) {
                try {
                    $product = $this->productRepository->get($sku);
                    $ids[]   = $product->getId();
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        return $ids;
    }

    /**
     * @param Column $column
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() === 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif ($productIds) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpmassproductactions/product_grid/upSellProducts');
    }

    /**
     * @param object $row
     *
     * @return string
     * @SuppressWarnings(Unused)
     */
    public function getRowUrl($row)
    {
        return '';
    }
}
