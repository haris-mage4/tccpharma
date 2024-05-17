<?php

/*Quote Filters*/

declare(strict_types=1);

namespace Amasty\RequestQuote\Api;

interface FiltersInterface
{
    public function isFilterable($post);
    public function filter($quote, $post);
}
