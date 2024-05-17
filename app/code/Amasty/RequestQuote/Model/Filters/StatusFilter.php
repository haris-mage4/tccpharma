<?php

declare(strict_types=1);

namespace Amasty\RequestQuote\Model\Filters;

use Amasty\RequestQuote\Api\FiltersInterface;

class StatusFilter implements FiltersInterface
{
    public function isFilterable($post): bool
    {
        return isset($post['status']);
    }

    public function filter($quote, $post)
    {
        if($post['status'] == 3){
            $quote->getSelect()->joinLeft(
                ["qi_status" => "quote_item"],
                'main_table.entity_id = qi_status.quote_id',
                []
            )->where('qi_status.is_cancel_by_admin = ?', $post['status'])
                ->group('main_table.entity_id');
        } else{
            $quote->getSelect()->joinLeft(
                ["qi_status" => "quote_item"],
                'main_table.entity_id = qi_status.quote_id',
                []
            )->where("qi_status.approval_status = '" . $post['status'] . "'")
                ->group('main_table.entity_id');
        }

        return $quote;
    }
}
