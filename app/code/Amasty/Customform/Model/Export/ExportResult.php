<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Export;

class ExportResult implements ExportResultInterface
{
    /**
     * @var string
     */
    private $rawResult;

    /**
     * @var string
     */
    private $resultName;

    public function __construct(
        string $rawResult = '',
        string $name = ''
    ) {
        $this->resultName = $name;
        $this->rawResult = $rawResult;
    }

    public function getRaw(): string
    {
        return $this->rawResult;
    }

    public function getName(): string
    {
        return $this->resultName;
    }
}
