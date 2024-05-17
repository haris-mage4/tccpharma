<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\ViewModel\Answser\Email;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class FieldsDataProvider implements ArgumentInterface
{
    private $fieldsData = [];

    /**
     * @return DataObject[]
     */
    public function getFieldsData(): array
    {
        return $this->fieldsData;
    }

    /**
     * @param DataObject[] $fieldsData
     */
    public function setFieldsData(array $fieldsData): void
    {
        $this->fieldsData = $fieldsData;
    }
}
