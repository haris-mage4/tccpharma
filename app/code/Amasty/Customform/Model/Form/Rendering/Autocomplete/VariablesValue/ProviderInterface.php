<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete\VariablesValue;

interface ProviderInterface
{
    /**
     * @param string $variableName
     *
     * @return bool
     */
    public function isCanRetrieve(string $variableName): bool;

    /**
     * @param string $variableName
     *
     * @return string
     */
    public function getValue(string $variableName): string;
}
