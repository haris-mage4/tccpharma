<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\Ordernotes\Model\ResourceModel\Notes;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * ResourceModel collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Ulmod\Ordernotes\Model\Notes::class,
            \Ulmod\Ordernotes\Model\ResourceModel\Notes::class
        );
    }
}
