<?php

namespace Eagle\Sorting\Plugin\CatalogSearch\Block;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class ResultPlugin {

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer; 

    public function __construct(
      LayerResolver $layerResolver      
    ) {

        $this->catalogLayer = $layerResolver->get();             
    }

    public function aroundSetListOrders(
        \Magento\CatalogSearch\Block\Result $subject,
        \Closure $proceed       
    )
    {
        $category = $this->catalogLayer->getCurrentCategory();
        /* @var $category \Magento\Catalog\Model\Category */
        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);
        $availableOrders['stock'] = __('Availability');
        $subject->getListBlock()->setAvailableOrders(
            $availableOrders
        )->setDefaultDirection(
            'desc'
        )->setDefaultSortBy(
            'stock'
        );

        return $subject;
    }
}