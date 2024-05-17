<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Config\Source;

use Amasty\Customform\Model\ResourceModel\Form\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Form implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray(): array
    {
        $result = [];
        $collection = $this->collectionFactory->create();

        foreach ($collection as $item) {
            $result[] = ['value' => $item->getFormId(), 'label' => $item->getTitle()];
        }

        return $result;
    }

    public function toArray(): array
    {
        $optionArray = $this->toOptionArray();

        return array_column($optionArray, 'label', 'value');
    }
}
