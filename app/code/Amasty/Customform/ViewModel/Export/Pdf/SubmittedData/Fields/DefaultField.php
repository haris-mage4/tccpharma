<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\ViewModel\Export\Pdf\SubmittedData\Fields;

class DefaultField implements FieldValueInterface
{
    use FieldViewModelTrait;

    /**
     * @var string
     */
    private $fieldValue;
}
