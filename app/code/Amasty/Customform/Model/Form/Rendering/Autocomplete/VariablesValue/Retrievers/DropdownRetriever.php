<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete\VariablesValue\Retrievers;

use Magento\Customer\Model\Attribute as AttributeModel;

class DropdownRetriever implements RetrieverInterface
{
    public function retrieve(AttributeModel $attribute, string $value): string
    {
        $result = '';

        foreach ($attribute->getOptions() as $option) {
            if ($option->getValue() == $value) {
                $result = (string) $option->getLabel();
                break;
            }
        }

        return $result;
    }
}
