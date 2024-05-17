<?php
namespace Magemonkeys\Product\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data;

class SpecialGroups extends Column
{
    /**
     * SpecialGroups constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resourceConnection,
        GroupRepositoryInterface $groupRepositoryInterface,
        Data $priceHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->resourceConnection = $resourceConnection;
        $this->groupRepositoryInterface = $groupRepositoryInterface;
        $this->priceHelper = $priceHelper;

       
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $connection = $this->resourceConnection->getConnection();  
        $tableName = $this->resourceConnection->getTableName('catalog_product_entity_tier_price');
        $data_array=[];
        
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {
            
                if (!empty($item['customer_group'])) {
                    exit;
                 $customerGroupIds = explode(',', $item['customer_group']);
                }
                $select = $connection->select()->from($tableName)
                    ->where('entity_id = ?', $item['entity_id']);
                $tierPrices = $connection->fetchAll($select);
        
                foreach($tierPrices as $value){
            
                    $group = $this->groupRepositoryInterface->getById($value['customer_group_id']);
                    $customerGroupName = $group->getCode();
                    $_price = $value['value'];
                    $per_price = $value['percentage_value'];
                    
                    if($_price == 0 && $per_price >= 0){
                    
                        $data_array[$item['entity_id']][]=$customerGroupName.' :: '.round($per_price,0).'%';
                    
                    }else{
                    
                        $data_array[$item['entity_id']][]=$customerGroupName.' :: '.    $this->priceHelper->currency($_price,true,false);
                    
                    }
                
                }
                if (array_key_exists($item['entity_id'],$data_array)){
                    
                    $all_cus= implode(",",$data_array[$item['entity_id']]);
                    $item[$this->getData('name')] = $all_cus;
                
                }
  
            }
        }

        return $dataSource;
    }

   

}
