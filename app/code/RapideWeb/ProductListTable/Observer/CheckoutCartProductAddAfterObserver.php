<?php

namespace RapideWeb\ProductListTable\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

class CheckoutCartProductAddAfterObserver implements ObserverInterface
{
    /**
     * @var LayoutInterface
     */
    protected LayoutInterface $_layout;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;
    /**
     * @var RequestInterface
     */
    protected RequestInterface $_request;
    /**
     * @var Json|mixed
     */
    private $serializer;

    /**
     * @param StoreManagerInterface $storeManager
     * @param LayoutInterface $layout
     * @param RequestInterface $request
     * @param Json|null $serializer
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LayoutInterface $layout,
        RequestInterface $request,
        Json $serializer = null
    )
    {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_request = $request;

        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /* @var Item $item */
        $item = $observer->getQuoteItem();
        $additionalOptions = array();
        if ($additionalOption = $item->getOptionByCode('additional_options')){
            $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
        }
        $post = $this->_request->getParams();
        if(isset($post['do_not_substitute']))
        {
            $additionalOptions[] = [
                'label' => 'Do Not Substitute',
                'value' => "Yes"
            ];
        }else{
            $additionalOptions[] = [
                'label' => 'Do Not Substitute',
                'value' => "No"
            ];
        }
        if(count($additionalOptions) > 0)
        {
            $item->addOption(array(
                'code' => 'additional_options',
                'value' => $this->serializer->serialize($additionalOptions)
            ));
        }
    }
}
