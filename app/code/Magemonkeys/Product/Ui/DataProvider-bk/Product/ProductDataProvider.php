<?php 
namespace Magemonkeys\Product\Ui\DataProvider\Product;

class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
	public function addFilter(\Magento\Framework\Api\Filter $filter)
	{
		if($filter->getField() == 'customer_group'){
			// exit;
		 	$customerGroupsArray = $filter->getValue();
		 	$this->getCollection()->getSelect()->join(
    			['tier_price' => $this->getCollection()->getTable('catalog_product_entity_tier_price')],
    			'e.entity_id = tier_price.entity_id',
    			[]
			)->group('e.entity_id');
			$this->getCollection()->getSelect()->where('tier_price.customer_group_id IN (?)', $customerGroupsArray);

        }
		else{

            parent::addFilter($filter);
        
        }
	// exit;
	}
}