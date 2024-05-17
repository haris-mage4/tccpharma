<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete;

class VariablesProcessor implements VariablesProcessorInterface
{
    const VARIABLE_REGEXP = '/(?<={)[a-zA-Z0-9_:]+(?=})/';

    public function extractVariables(string $text): array
    {
        preg_match_all(self::VARIABLE_REGEXP, $text, $variables);

        return array_merge(...$variables);
    }

    public function insertVariable(string $text, string $variable, string $variableValue): string
    {
        return str_replace("{{$variable}}", $variableValue, $text);
    }
}
