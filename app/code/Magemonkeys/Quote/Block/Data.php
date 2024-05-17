<?php
namespace Magemonkeys\Quote\Block;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
class Data extends \Magento\Framework\View\Element\Template
{    
    public function __construct(
        QuoteRepositoryInterface $repository,
        Status $status,
        CollectionFactory $quoteItemFactory
    ) {
        $this->repository=$repository;
        $this->status=$status;
        $this->quoteItemFactory = $quoteItemFactory;
    }
    public function getAdminRemark($quoteId) {
        try{
            
                $repo=$this->repository->get($quoteId);
                $data=json_decode($repo['remarks'], true);
                $status=$this->status->getStatusLabel($repo->getStatus());
                $data['status']=$status;
                return $data;
        }catch(\Exception $e){
            return null;
        }   
    }

      public function getRefererUrl()
    {
        return $this->request->getServer('HTTP_REFERER');
    }

    public function getRequestedPriceHtml($product_id,$quote_id)
    {
        $quoteItem = $this->quoteItemFactory->create()->addFieldToFilter('product_id',$product_id)
        ->addFieldToFilter('quote_id',$quote_id)->getLastItem();
        
        if(count($quoteItem->getData()) > 0){
            $additionalData = json_decode($quoteItem->getAdditionalData(),true);
            if (is_array($additionalData)) {
                return $additionalData['requested_custom_price']?$additionalData['requested_custom_price']:0;
            } else {
                return 0;
            }
        }
        else{
            
            return 0;
        }
    }
    


}
?>