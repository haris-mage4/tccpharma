<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Export\SubmitedData;

use Amasty\Customform\Api\Data\AnswerInterface;

interface ResultRendererInterface
{
    public function render(AnswerInterface $answer): string;
}
