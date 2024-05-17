<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\ViewModel\Answser\Answer;

use Amasty\Customform\ViewModel\Answser\CustomerAccount\Answer\View\CurrentAnswerProvider;
use Amasty\Customform\ViewModel\Answser\InformationDataProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Information implements ArgumentInterface
{
    /**
     * @var InformationDataProvider
     */
    private $informationDataProvider;

    /**
     * @var CurrentAnswerProvider
     */
    private $currentFormProvider;

    public function __construct(
        InformationDataProvider $informationDataProvider,
        CurrentAnswerProvider $currentFormProvider
    ) {
        $this->informationDataProvider = $informationDataProvider;
        $this->currentFormProvider = $currentFormProvider;
    }

    public function getInformationData(): array
    {
        $answer = $this->currentFormProvider->getCurrentResponse();

        return $this->informationDataProvider->getInformationData($answer);
    }
}
