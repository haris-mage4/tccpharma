<?php

namespace RapideWeb\ProductListTable\Observer;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Item;

class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{
    private $quoteItems = [];
    private $quote = null;
    private $order = null;
    /**
     * @var Json|mixed
     */
    private $serializer;

    public function __construct(
        Json $serializer = null
    )
    {
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $this->quote = $observer->getQuote();
        $this->order = $observer->getOrder();
        // can not find a equivalent event for sales_convert_quote_item_to_order_item
        /* @var  Item $orderItem */
        foreach($this->order->getItems() as $orderItem)
        {
            if(!$orderItem->getParentItemId() && $orderItem->getProductType() == Type::TYPE_SIMPLE)
            {

                if($quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId())){
                    if ($additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options'))
                    {
                        if($additionalOptionsQuote)
                        {
                            $options = $orderItem->getProductOptions();
                            $options['additional_options'] = $this->serializer->unserialize($additionalOptionsQuote->getValue());
                            $orderItem->setProductOptions($options);
                        }

                    }
                }
            }
        }
    }
    private function getQuoteItemById($id)
    {
        if(empty($this->quoteItems))
        {
            /* @var  \Magento\Quote\Model\Quote\Item $item */
            foreach($this->quote->getItems() as $item)
            {
                //filter out config/bundle etc product
                if(!$item->getParentItemId() && $item->getProductType() == Type::TYPE_SIMPLE)
                {
                    $this->quoteItems[$item->getId()] = $item;
                }
            }
        }
        if(array_key_exists($id, $this->quoteItems))
        {
            return $this->quoteItems[$id];
        }
        return null;
    }
}
