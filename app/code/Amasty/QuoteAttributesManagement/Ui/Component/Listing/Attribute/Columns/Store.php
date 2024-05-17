<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Ui\Component\Listing\Attribute\Columns;

use Amasty\QuoteAttributes\Model\Attribute\Store\GetByAttributeId as GetStoresByAttributeId;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * @var GetStoresByAttributeId
     */
    private $getStoresByAttributeId;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        GetStoresByAttributeId $getStoresByAttributeId,
        array $components = [],
        array $data = [],
        $storeKey = 'stores'
    ) {
        parent::__construct($context, $uiComponentFactory, $systemStore, $escaper, $components, $data, $storeKey);
        $this->getStoresByAttributeId = $getStoresByAttributeId;
    }

    /**
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        if (!isset($item[$this->getData('name')])) {
            $item[$this->getData('name')] = $this->getStoresByAttributeId->execute(
                (int) $item[$item['id_field_name']]
            );
        }

        return parent::prepareItem($item);
    }
}
