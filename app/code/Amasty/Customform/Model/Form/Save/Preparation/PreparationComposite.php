<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Save\Preparation;

class PreparationComposite implements PreparationInterface
{
    /**
     * @var PreparationInterface[]
     */
    private $preparationProcessors;

    /**
     * @param PreparationInterface[] $preparationProcessors
     */
    public function __construct(
        array $preparationProcessors = []
    ) {
        $this->preparationProcessors = $preparationProcessors;
    }

    public function prepare(array $formData): array
    {
        foreach ($this->preparationProcessors as $preparationProcessor) {
            $formData = $preparationProcessor->prepare($formData);
        }

        return $formData;
    }
}
