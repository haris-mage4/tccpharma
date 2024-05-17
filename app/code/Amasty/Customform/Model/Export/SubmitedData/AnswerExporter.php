<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Export\SubmitedData;

use Amasty\Customform\Api\Answer\AnswerExporterInterface;
use Amasty\Customform\Api\Data\AnswerInterface;
use Amasty\Customform\Model\Export\ExportResultFactory;
use Amasty\Customform\Model\Export\ExportResultInterface;

class AnswerExporter implements AnswerExporterInterface
{
    /**
     * @var ResultRendererInterface
     */
    private $resultRenderer;

    /**
     * @var ExportResultFactory
     */
    private $exportResultFactory;

    /**
     * @var ResultNameGeneratorInterface
     */
    private $resultNameGenerator;

    public function __construct(
        ResultRendererInterface $resultRenderer,
        ExportResultFactory $exportResultFactory,
        ResultNameGeneratorInterface $resultNameGenerator
    ) {
        $this->resultRenderer = $resultRenderer;
        $this->exportResultFactory = $exportResultFactory;
        $this->resultNameGenerator = $resultNameGenerator;
    }

    public function export(AnswerInterface $answer): ExportResultInterface
    {
        $result = $this->resultRenderer->render($answer);

        return $this->exportResultFactory->create([
            'rawResult' => $result,
            'name' => $this->resultNameGenerator->generateName($answer)
        ]);
    }

    /**
     * @param iterable $answerSource
     * @return ExportResultInterface[]
     */
    public function exportMultiple(iterable $answerSource): iterable
    {
        foreach ($answerSource as $answer) {
            yield $this->export($answer);
        }
    }
}
