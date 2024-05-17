<?php

/*Order Filters*/

declare(strict_types=1);

namespace Ulmod\Ordernotes\Api;

interface FiltersInterface
{
    public function isFilterable($post);
    public function filter($order, $post);
}
