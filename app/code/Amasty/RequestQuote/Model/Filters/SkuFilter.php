<?php

declare(strict_types=1);

namespace Amasty\RequestQuote\Model\Filters;

use Amasty\RequestQuote\Api\FiltersInterface;

class SkuFilter implements FiltersInterface
{
    public function isFilterable($post): bool
    {
        return  (!empty($post['sku']));
    }

    public function filter($quote, $post)
    {
        $quote->getSelect()->joinLeft(
            ["qi_sku" => "quote_item"],
            'main_table.entity_id = qi_sku.quote_id',
            []
        )->where('qi_sku.sku LIKE ?', '%' . $post['sku'] . '%')
        ->group('main_table.entity_id');

        return $quote;
    }
}
