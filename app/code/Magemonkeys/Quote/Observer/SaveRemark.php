<?php
namespace Magemonkeys\Quote\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Stathmos\QuoteCommunication\Model\RemarkCommentFactory;


class SaveRemark implements ObserverInterface
{
    public function __construct(
        RemarkCommentFactory $quote_factory
    ) {
        $this->quote_factory = $quote_factory;
    }

    public function execute(Observer $observer)
    {
        try{
            $quote = $observer->getData('quote');
            $data = $quote->debug();
            $remarkCommentModel = $this->quote_factory->create();
            if(array_key_exists('remarks',$data)){
                $quote_remark = json_decode($data['remarks'],true);
                if(array_key_exists('customer_note',$quote_remark)){
                    $customer_remark = $quote_remark['customer_note'];
                        $remarkCommentModel->addData([
                            "quote_id" => $data['entity_id'],
                            "customer_id" => $data['customer_id'],
                            "customer_name" => $data['customer_name'],
                            "remark_comment" => $customer_remark,
                        ]);
                    $remarkCommentModel->save();
                }
            }
        }catch(\Exception $e){
            
        }

    }
}
