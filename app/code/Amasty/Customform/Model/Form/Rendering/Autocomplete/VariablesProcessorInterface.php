<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete;

interface VariablesProcessorInterface
{
    /**
     * @param string $text
     *
     * @return string[]
     */
    public function extractVariables(string $text): array;

    /**
     * @param string $text
     * @param string $variable
     * @param string $variableValue
     *
     * @return string
     */
    public function insertVariable(string $text, string $variable, string $variableValue): string;
}
