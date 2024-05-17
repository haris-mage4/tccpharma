<?php

/*Order Filters*/

declare(strict_types=1);

namespace Ulmod\Ordernotes\Model\Filters;

use Ulmod\Ordernotes\Api\FiltersInterface;

class NameFilter implements FiltersInterface
{
    public function isFilterable($post): bool
    {
        return  (!empty($post['name']));
    }

    public function filter($order, $post)
    {
        $order->join(
            ["soi" => "sales_order_item"],
            'main_table.entity_id = soi.order_id
                AND
                soi.product_type in ("simple","downloadable") AND main_table.created_at = soi.created_at',
            array('name')
        )->addFieldToFilter('soi.name', array('like' => '%'.$post['name'].'%'));

        return $order;
    }
}