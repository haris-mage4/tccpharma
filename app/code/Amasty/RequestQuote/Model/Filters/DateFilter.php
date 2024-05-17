<?php

/*Quote Filters*/

declare(strict_types=1);

namespace Amasty\RequestQuote\Model\Filters;

use Amasty\RequestQuote\Api\FiltersInterface;

class DateFilter implements FiltersInterface
{
    public function isFilterable($post): bool
    {
        return  (!empty($post['from_date']) || !empty($post['to_date']));
    }

    public function filter($quote, $post)
    {
        if (!empty($post['from_date']) && !empty($post['to_date'])) {
            $fromDate = date("Y-m-d 00:00:00", strtotime($post['from_date']));
            $toDate = date("Y-m-d 23:59:59", strtotime($post['to_date']));
            
            $quote->addFieldToFilter('main_table.created_at', ['gteq' => $fromDate])
                  ->addFieldToFilter('main_table.created_at', ['lteq' => $toDate]);
        } elseif (!empty($post['from_date'])) {
            $fromDate = date("Y-m-d", strtotime($post['from_date']));
            $quote->addFieldToFilter('main_table.created_at', ['like' => $fromDate . '%']);
        } elseif (!empty($post['to_date'])) {
            $toDate = date("Y-m-d", strtotime($post['to_date']));
            $quote->addFieldToFilter('main_table.created_at', ['like' => $toDate . '%']);
        }
    
        return $quote;
    }
    
}