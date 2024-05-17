<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\ViewModel\Export\Pdf\SubmittedData\Fields;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class FieldValueModelFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $defaultViewModelClass;

    public function __construct(
        ObjectManagerInterface $objectManager,
        string $defaultViewModelClass = DefaultField::class
    ) {
        $this->objectManager = $objectManager;
        $this->defaultViewModelClass = $defaultViewModelClass;
    }

    public function create(string $type, array $arguments = []): FieldValueInterface
    {
        $viewModelClass = sprintf('%s\%s', __NAMESPACE__, ucfirst($type));
        $viewModelClass = class_exists($viewModelClass) ? $viewModelClass : $this->defaultViewModelClass;
        $viewModel = $this->objectManager->create($viewModelClass, $arguments);

        if (false === $viewModel instanceof FieldValueInterface) {
            throw new LocalizedException(__('View Model must implements %1', FieldValueInterface::class));
        }

        return $viewModel;
    }
}
