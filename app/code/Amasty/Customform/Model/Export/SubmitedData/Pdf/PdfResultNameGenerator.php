<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Export\SubmitedData\Pdf;

use Amasty\Customform\Api\Data\AnswerInterface;
use Amasty\Customform\Controller\Adminhtml\Answer\ExportGridToPdf;
use Amasty\Customform\Model\CachingFormProvider;
use Amasty\Customform\Model\Export\SubmitedData\ResultNameGeneratorInterface;

class PdfResultNameGenerator implements ResultNameGeneratorInterface
{
    const DEFAULT_NAME_PREFIX = ExportGridToPdf::DEFAULT_FILE_PREFIX;

    /**
     * @var CachingFormProvider
     */
    private $cachingFormProvider;

    public function __construct(
        CachingFormProvider $cachingFormProvider
    ) {
        $this->cachingFormProvider = $cachingFormProvider;
    }

    public function generateName(AnswerInterface $answer): string
    {
        $form = $this->cachingFormProvider->getById((int) $answer->getFormId());
        $namePrefix = $form === null ? self::DEFAULT_NAME_PREFIX : $form->getCode();

        return sprintf('%s_%d.pdf', $namePrefix, (int) $answer->getAnswerId());
    }
}
