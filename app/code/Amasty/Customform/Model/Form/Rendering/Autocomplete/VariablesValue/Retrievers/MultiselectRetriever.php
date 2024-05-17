<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete\VariablesValue\Retrievers;

use Magento\Customer\Model\Attribute as AttributeModel;

class MultiselectRetriever implements RetrieverInterface
{
    /**
     * @var DropdownRetriever
     */
    private $dropdownRetriever;

    public function __construct(
        DropdownRetriever $dropdownRetriever
    ) {
        $this->dropdownRetriever = $dropdownRetriever;
    }

    public function retrieve(AttributeModel $attribute, string $value): string
    {
        $values = array_map('trim', explode(',', $value));
        $labels = array_map(function (string $singleValue) use ($attribute): string {
            return $this->dropdownRetriever->retrieve($attribute, $singleValue);
        }, $values);

        return join(', ', $labels);
    }
}
