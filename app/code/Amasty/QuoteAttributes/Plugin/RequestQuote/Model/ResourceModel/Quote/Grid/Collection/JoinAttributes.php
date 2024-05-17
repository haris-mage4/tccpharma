<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Plugin\RequestQuote\Model\ResourceModel\Quote\Grid\Collection;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity\Collection as QuoteEntityCollection;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity\CollectionFactory as QuoteEntityCollectionFactory;
use Amasty\QuoteAttributes\Ui\Component\Quote\Listing\AttributeProvider;
use Amasty\RequestQuote\Model\ResourceModel\Quote\Grid\Collection as QuoteCollection;

/**
 * Adds to the quote collection attributes that are configured in the Field Configuration to be added to the quote grid
 */
class JoinAttributes
{
    public const JOINED_QUOTE_ATTRIBUTES_FLAG = 'quote_attributes_joined';
    /**
     * @var QuoteEntityCollectionFactory
     */
    private $quoteEntityCollectionFactory;
    /**
     * @var AttributeProvider
     */
    private $attributeProvider;

    public function __construct(
        QuoteEntityCollectionFactory $quoteEntityCollectionFactory,
        AttributeProvider $attributeProvider
    ) {
        $this->quoteEntityCollectionFactory = $quoteEntityCollectionFactory;
        $this->attributeProvider = $attributeProvider;
    }

    /**
     * @param QuoteCollection $quoteCollection
     * @return void
     */
    public function beforeLoad(QuoteCollection $quoteCollection): void
    {
        if ($quoteCollection->isLoaded() || $quoteCollection->getFlag(self::JOINED_QUOTE_ATTRIBUTES_FLAG)) {
            return;
        }

        $attributeCodesToSelect = [];
        /** @var QuoteEntityCollection $quoteEntityCollection */
        $quoteEntityCollection = $this->quoteEntityCollectionFactory->create();
        foreach ($this->attributeProvider->execute() as $attribute) {
            $quoteEntityCollection->addAttributeToSelect($attribute->getAttributeCode(), 'left');
            $attributeCodesToSelect[] = $attribute->getAttributeCode();
        }

        $quoteCollection->getSelect()->joinLeft(
            ['quote_entity' => $quoteEntityCollection->getSelect()],
            sprintf('main_table.entity_id = quote_entity.%s', QuoteEntityInterface::QUOTE_ID),
            $attributeCodesToSelect
        );

        $quoteCollection->setFlag(self::JOINED_QUOTE_ATTRIBUTES_FLAG, true);
    }
}
