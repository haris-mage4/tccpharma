<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class FacilityName extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        // if (isset($dataSource['data']['items'])) {
        //     foreach ($dataSource['data']['items'] as &$item) {
        //         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //         if (isset($item['entity_id'])) {
        //             $facilityName = '';
        //             $model = $objectManager->create(\Amasty\QuoteAttributes\Model\QuoteEntity::class)->load($item['entity_id']);
        //             $quote_attribute_data = $model->getData();
        //             unset($quote_attribute_data['entity_id']);
        //             unset($quote_attribute_data['quote_id']);
        //             foreach ($quote_attribute_data as $key => $value) {
        //                 $attributeCode = $key;
        //                 $attributeValue = $value;
        //                 $attributeOptions = $model->getResource()->getAttribute($attributeCode)->getSource()->getAllOptions();
        //                 if ($attributeCode == 'facility') {

        //                     $facilityName = $attributeValue;
        //                 }
        //             }
        //             $item[$this->getData('name')] = $facilityName;
        //         }
        //     }
        // }

        return $dataSource;
    }
}
