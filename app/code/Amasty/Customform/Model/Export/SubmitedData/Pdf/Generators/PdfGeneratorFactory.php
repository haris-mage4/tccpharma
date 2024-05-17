<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Export\SubmitedData\Pdf\Generators;

use Amasty\Customform\Exceptions\ExternalDependencyNotFoundException;
use Amasty\Customform\Model\ExternalLibsChecker;
use Magento\Framework\ObjectManagerInterface;

class PdfGeneratorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ExternalLibsChecker
     */
    private $externalLibsChecker;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ExternalLibsChecker $externalLibsChecker
    ) {
        $this->objectManager = $objectManager;
        $this->externalLibsChecker = $externalLibsChecker;
    }

    /**
     * @param array $params
     * @return PdfGeneratorInterface
     * @throws ExternalDependencyNotFoundException
     */
    public function create(array $params = []): PdfGeneratorInterface
    {
        $this->externalLibsChecker->checkPdfDom();
        /** @phpstan-ignore-next-line **/
        $domPdf = $this->objectManager->create(\Dompdf\Dompdf::class, $params);

        /** @phpstan-ignore-next-line **/
        return $this->objectManager->create(PdfGenerator::class, ['domPdf' => $domPdf]);
    }
}
