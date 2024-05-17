<?php
namespace Stathmos\ExtendedOrdersGrid\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
use Psr\Log\LoggerInterface;

/**
 * Class AddDataToOrdersGrid
 */
class AddDataToOrdersGrid
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * AddDataToOrdersGrid constructor.
     *
     * @param LoggerInterface $customLogger
     * @param array $data
     */
    public function __construct(
        LoggerInterface $customLogger,
        array $data = []
    ) {
        $this->logger = $customLogger;
    }

    /**
     * @param CollectionFactory $subject
     * @param OrderGridCollection $collection
     * @param $requestName
     * @return mixed
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
    	$requestArrayName = array('sales_order_grid_data_source','amasty_quote_grid_data_source');
        if (!in_array($requestName, $requestArrayName)) {
            return $collection;
        }

        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            try {
                // Add product's name column
                $this->addProductsNameColumn($collection);
            } catch (\Zend_Db_Select_Exception $selectException) {
                // Do nothing in that case
                $this->logger->log(100, $selectException);
            }
        } elseif($collection->getMainTable() === $collection->getResource()->getTable('quote')){

            $collection->getSelect()->join(
                 ["qi" => "quote_item"],
                 'main_table.entity_id = qi.quote_id',
                 ['sku' => 'GROUP_CONCAT(DISTINCT qi.sku)',
                  'name' => 'GROUP_CONCAT(DISTINCT qi.name)'
                 ]
             )->join(
                    ['cpev' => 'catalog_product_entity_varchar'],
                    'qi.product_id = cpev.entity_id',
                    ['ndc' => 'GROUP_CONCAT(cpev.value)']
                )->where('cpev.attribute_id = 182')
             //->where('qi.store_id = cpev.store_id')
                    ->group("qi.quote_id");
        }

        return $collection;
    }

    /**
     * Adds products name column to the orders grid collection
     *
     * @param OrderGridCollection $collection
     * @return OrderGridCollection
     */
    private function addProductsNameColumn(OrderGridCollection $collection): OrderGridCollection
    {
        // Get original table name
        $orderItemsTableName = $collection->getResource()->getTable('sales_order_item');
        // Create new select instance
        $itemsTableSelectGrouped = $collection->getConnection()->select();
        // Add table with columns which must be selected (skip useless columns)
        $itemsTableSelectGrouped->from(
            $orderItemsTableName,
            [
                'name'     => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT name SEPARATOR \',\')'),
                'order_id' => 'order_id'
            ]
        );
        // Group our select to make one column for one order
        $itemsTableSelectGrouped->group('order_id');
        // Add our sub-select to main collection with only one column: name
        $collection->getSelect()
                   ->joinLeft(
                       ['soi' => $itemsTableSelectGrouped],
                       'soi.order_id = main_table.entity_id',
                       ['name']
                   );

        return $collection;
    }
}
