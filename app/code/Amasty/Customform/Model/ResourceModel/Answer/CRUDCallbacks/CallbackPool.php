<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\ResourceModel\Answer\CRUDCallbacks;

class CallbackPool implements \IteratorAggregate
{
    /**
     * @var CallbackInterface[]
     */
    private $callbacks;

    public function __construct(
        $callbacks = []
    ) {
        $this->callbacks = $callbacks;
    }

    public function getIterator(): iterable
    {
        return new \ArrayIterator($this->callbacks);
    }
}
