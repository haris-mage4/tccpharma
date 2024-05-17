<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Di;

use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerMetaProvider;
use Magento\Framework\ObjectManagerInterface;

class ClassExistsWrapper
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isShared;

    /**
     * @var bool
     */
    private $isProxy;

    /**
     * @var object
     */
    private $subject;

    /**
     * @var ObjectManagerMetaProvider
     */
    private $diMetaProvider;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectManagerMetaProvider $diMetaProvider,
        ?string $name = '',
        ?bool $isShared = false,
        ?bool $isProxy = false
    ) {
        $this->objectManager = $objectManager;
        $this->diMetaProvider = $diMetaProvider;
        $this->name = $name;
        $this->isShared = $isShared;
        $this->isProxy = $isProxy;
    }

    public function __call(string $method, array $arguments)
    {
        $result = false;

        if ($this->canCreateObject()) {
            $subject = $this->getSubject();
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $result = call_user_func_array([$subject, $method], $arguments);
        }

        return $result;
    }

    private function getSubject(): object
    {
        if ($this->isProxy && $this->subject) {
            return $this->subject;
        }

        if ($this->isShared) {
            $subject = $this->objectManager->get($this->name);
        } else {
            $subject = $this->objectManager->create($this->name);
        }

        if ($this->isProxy) {
            $this->subject = $subject;
        }

        return $subject;
    }

    private function canCreateObject(): bool
    {
        $canAutoload = (class_exists($this->name) || interface_exists($this->name));
        $canGetObjectByDI = $this->isVirtualType();

        return $this->name && ($canAutoload || $canGetObjectByDI);
    }

    private function isVirtualType(): bool
    {
        $instanceType = $this->diMetaProvider->getInstanceType($this->name);

        return $instanceType !== $this->name;
    }
}
