<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\ViewModel\Export\Pdf\SubmittedData\Fields;

use Amasty\Customform\Api\Answer\GetAttachedFileUrlInterface;

class File implements FieldValueInterface
{
    use FieldViewModelTrait;

    /**
     * @var string
     */
    private $fieldValue;

    /**
     * @var GetAttachedFileUrlInterface
     */
    private $getAttachedFileUrl;

    public function __construct(
        GetAttachedFileUrlInterface $getAttachedFileUrl
    ) {
        $this->getAttachedFileUrl = $getAttachedFileUrl;
    }

    public function getFieldValue(): array
    {
        $files = (array) $this->fieldValue;
        $result = [];

        foreach ($files as $fileName) {
            $result[$fileName] = $this->getAttachedFileUrl->execute($fileName);
        }

        return $result;
    }
}
