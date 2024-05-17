<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete\VariablesValue\Retrievers;

use Magento\Customer\Model\Attribute as AttributeModel;

class CompositeRetriever implements RetrieverInterface
{
    /**
     * @var RetrieverInterface[]
     */
    private $retrieverPool;

    /**
     * @var DummyRetriever
     */
    private $dummyRetriever;

    public function __construct(
        DummyRetriever $dummyRetriever,
        array $retrieversPool = []
    ) {
        $this->retrieverPool = $retrieversPool;
        $this->dummyRetriever = $dummyRetriever;
    }

    public function retrieve(AttributeModel $attribute, string $value): string
    {
        $retriever = $this->getRetriever($attribute);

        return $retriever->retrieve($attribute, $value);
    }

    private function getRetriever(AttributeModel $attribute): RetrieverInterface
    {
        return $this->retrieverPool[$attribute->getFrontendInput()] ?? $this->dummyRetriever;
    }
}
