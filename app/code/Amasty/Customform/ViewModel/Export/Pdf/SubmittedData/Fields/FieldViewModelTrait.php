<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\ViewModel\Export\Pdf\SubmittedData\Fields;

trait FieldViewModelTrait
{
    /**
     * @var string
     */
    private $fieldValue;

    public function getFieldValue()
    {
        return $this->fieldValue;
    }

    public function setFieldValue(string $fieldValue): void
    {
        $this->fieldValue = $fieldValue;
    }
}
