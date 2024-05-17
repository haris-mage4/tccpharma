<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Ui\Component\Quote\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns as ListingColumns;

class Columns extends ListingColumns
{
    public const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /**
     * @var AttributeProvider
     */
    private $attributeProvider;

    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    public function __construct(
        AttributeProvider $attributeProvider,
        ColumnFactory $columnFactory,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->attributeProvider = $attributeProvider;
        $this->columnFactory = $columnFactory;
    }

    /**
     * @return void
     */
    public function prepare(): void
    {
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;

        foreach ($this->attributeProvider->execute() as $attribute) {
            if (isset($this->components[$attribute->getAttributeCode()])) {
                continue;
            }

            $config['sortOrder'] = ++$columnSortOrder;
            $column = $this->columnFactory->create($attribute, $this->getContext(), $config);
            $column->prepare();
            $this->addComponent($attribute->getAttributeCode(), $column);
        }

        parent::prepare();
    }
}
