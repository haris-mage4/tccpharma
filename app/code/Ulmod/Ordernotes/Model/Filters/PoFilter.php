<?php

/*Order Filters*/

declare(strict_types=1);

namespace Ulmod\Ordernotes\Model\Filters;

use Ulmod\Ordernotes\Api\FiltersInterface;

class PoFilter implements FiltersInterface
{
    public function isFilterable($post): bool
    {
        return  (!empty($post['po_number']));
    }

    public function filter($order, $post)
    {
        $order->join(
            ["sop" => "sales_order_payment"],
            'main_table.entity_id = sop.parent_id',
            array('po_number')
        )->addFieldToFilter('sop.po_number', array('like' => '%'.$post['po_number'].'%'));

        return $order;
    }
}