<?php
namespace Magemonkeys\CompanyName\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
class ObserverforAddCustomVariable implements ObserverInterface
{
    protected $customerRepository;
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getEvent()->getTransport();
        if ($transport->getOrder() != null) 
        {
            $customer = $this->customerRepository->getById($transport->getOrder()->getCustomerId());
            if ($customer->getCustomAttribute('companyname')) 
            {
                $transport['companyname'] = $customer->getCustomAttribute('companyname')->getValue();
            }
        }
    }
}