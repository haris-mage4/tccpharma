<?php

namespace Amasty\RequestQuote\ViewModel;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class Data implements ArgumentInterface
{
    /**
     * @var QuoteRepositoryInterface
     */
    protected $repository;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var Session
     */
    protected $adminSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    public function __construct(
        QuoteRepositoryInterface $repository,
        Status $status,
        Session $adminSession,
        CartRepositoryInterface $cartRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->repository = $repository;
        $this->status = $status;
        $this->adminSession = $adminSession;
        $this->cartRepository = $cartRepository;
        $this->customerRepository = $customerRepository;
    }


    public function getAdminRemark($quoteId)
    {
        try {
            $repo = $this->repository->get($quoteId);
            $data = json_decode($repo['remarks'], true);
            $status = $this->status->getStatusLabel($repo->getStatus());
            $data['status'] = $status;
            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getRefererUrl()
    {
        return $this->adminSession->getData('adminhtml_redirect_referer');
    }

    public function getCurrentAdminUserName()
    {
        return $this->adminSession->getUser()->getUsername();
    }

    public function getCustomerInfoByQuoteId($quoteId)
    {
        $quote = $this->cartRepository->get($quoteId);
        $customerId = $quote->getCustomerId();

        if (!$customerId) {
            return null;
        }

        $customer = $this->customerRepository->getById($customerId);

        $customerEmail = $customer->getEmail();
        $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();

        return [
            'customer_id' => $customerId,
            'customer_email' => $customerEmail,
            'customer_name' => $customerName,
        ];
    }
}
