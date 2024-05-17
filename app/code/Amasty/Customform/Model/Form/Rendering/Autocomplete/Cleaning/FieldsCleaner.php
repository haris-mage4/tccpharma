<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete\Cleaning;

use Amasty\Base\Model\Serializer;
use Amasty\Customform\Model\Form\Rendering\Autocomplete\VariablesProcessorInterface;
use Amasty\Customform\Model\Submit;
use Amasty\Customform\Model\Utils\CustomerInfo;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;

class FieldsCleaner
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var VariablesProcessorInterface
     */
    private $variablesProcessor;

    /**
     * @var CustomerInfo
     */
    private $customerInfo;

    /**
     * @var State
     */
    private $state;

    public function __construct(
        Serializer $serializer,
        VariablesProcessorInterface $variablesProcessor,
        State $state
    ) {
        $this->serializer = $serializer;
        $this->variablesProcessor = $variablesProcessor;
        $this->state = $state;
    }

    public function cleanJson(string $json): string
    {
        if ($this->state->getAreaCode() != Area::AREA_ADMINHTML) {
            $formConfig = $this->serializer->unserialize($json);

            foreach ($formConfig as &$page) {
                foreach ($page as &$fieldConfig) {
                    $variables = $this->variablesProcessor->extractVariables($fieldConfig[Submit::VALUE] ?? '');

                    if (!empty($variables)) {
                        $fieldConfig[Submit::VALUE] = '';
                    }
                }
            }

            $json = $this->serializer->serialize($formConfig);
        }

        return $json;
    }
}
