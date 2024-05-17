<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Utils;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Customer as CustomerDataModel;
use Magento\Customer\Model\SessionFactory;

class CustomerInfo
{
    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var ?Customer
     */
    private $currentCustomer;

    /**
     * @var ?CustomerDataModel
     */
    private $customerDataModel;

    public function __construct(
        SessionFactory $sessionFactory
    ) {
        $this->sessionFactory = $sessionFactory;
    }

    public function isLoggedIn(): bool
    {
        return $this->getCurrentCustomer() !== null;
    }

    public function getCurrentCustomer(): ?Customer
    {
        if ($this->currentCustomer === null) {
            $session = $this->sessionFactory->create();

            if ($session->isLoggedIn()) {
                $this->currentCustomer = $session->getCustomer();
            }
        }

        return $this->currentCustomer;
    }

    public function getCustomerDataModel(): ?CustomerDataModel
    {
        if ($this->customerDataModel === null && $this->isLoggedIn()) {
            $this->customerDataModel = $this->getCurrentCustomer()->getDataModel();
        }

        return $this->customerDataModel;
    }
}
