<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\ResourceModel\Answer\CRUDCallbacks;

use Amasty\Customform\Api\Data\AnswerInterface;

interface CallbackInterface
{
    public function process(AnswerInterface $answer): void;
}
