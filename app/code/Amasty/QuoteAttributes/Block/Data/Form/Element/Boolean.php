<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Block\Data\Form\Element;

use Magento\Config\Model\Config\Source\Yesno as YesnoSource;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Escaper;

class Boolean extends Select
{
    /**
     * @var YesnoSource
     */
    private $yesnoSoruce;

    public function __construct(
        YesnoSource $yesnoSoruce,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        $this->yesnoSoruce = $yesnoSoruce;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->setValues($this->yesnoSoruce->toOptionArray());
    }
}
