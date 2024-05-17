<?php

declare(strict_types=1);

namespace Amasty\RequestQuote\Model\Filters;

use Amasty\RequestQuote\Api\FiltersInterface;

class NameFilter implements FiltersInterface
{
    public function isFilterable($post): bool
    {
        return  (!empty($post['name']));
    }

    public function filter($quote, $post)
    {
        $quote->getSelect()->joinLeft(
            ["qi_name" => "quote_item"],
            'main_table.entity_id = qi_name.quote_id',
            []
        )->where('qi_name.name LIKE ?', '%' . $post['name'] . '%')
        ->group('main_table.entity_id');

        return $quote;
    }
}
