<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_MassProductActions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\MassProductActions\Block\Adminhtml\Price\Edit;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;

/**
 * Class TierPrice
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Price\Edit
 */
class TierPrice extends Template
{
    /**
     * @var DirectoryHelper
     */
    protected $_directoryHelper;

    /**
     * @var ModuleManager
     */
    protected $_moduleManager;

    /**
     * @var GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteria;

    /**
     * @var CatalogHelper
     */
    protected $_catalogHelper;

    /**
     * @var WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * TierPrice constructor.
     *
     * @param Context $context
     * @param DirectoryHelper $directoryHelper
     * @param ModuleManager $moduleManager
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteria
     * @param CatalogHelper $catalogHelper
     * @param WebsiteFactory $websiteFactory
     * @param CurrencyFactory $currencyFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        DirectoryHelper $directoryHelper,
        ModuleManager $moduleManager,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteria,
        CatalogHelper $catalogHelper,
        WebsiteFactory $websiteFactory,
        CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        $this->_moduleManager   = $moduleManager;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteria  = $searchCriteria;
        $this->_catalogHelper   = $catalogHelper;
        $this->_websiteFactory  = $websiteFactory;
        $this->_currencyFactory = $currencyFactory;

        parent::__construct($context, $data);
    }

    /**
     * Get websites list
     *
     * @return array
     */
    public function getWebsites()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->_directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        if (!$this->_catalogHelper->isPriceGlobal()) {
            $websitesList = $this->_storeManager->getWebsites();
            foreach ($websitesList as $website) {
                /** @var Website $website */
                $websites[] = [
                    'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                    'value' => $website->getId(),
                ];
            }
        }

        return $websites;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        if (!$this->_moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroups = [
            [
                'label' => __('ALL GROUPS'),
                'value' => GroupInterface::CUST_GROUP_ALL,
            ]
        ];

        try {
            /** @var Collection $groups */
            $groups = $this->_groupRepository->getList($this->_searchCriteria->create());
            foreach ($groups->getItems() as $group) {
                /** @var Group $group */
                $customerGroups[] = [
                    'label' => $group->getCode(),
                    'value' => $group->getId(),
                ];
            }
        } catch (Exception $e) {
            $this->_logger->info($e->getMessage());
        }

        return $customerGroups;
    }

    /**
     * @return array
     */
    public function getPriceOptions()
    {
        $objectManager = ObjectManager::getInstance();
        $priceOptions  = $objectManager->create(\Magento\Catalog\Model\Config\Source\Product\Options\TierPrice::class);

        return $priceOptions->toOptionArray();
    }

    /**
     * @param string $websiteId
     *
     * @return string
     */
    public function getCurrencySymbolByWebsite($websiteId)
    {
        if ($websiteId === '0') {
            $defaultCurrencyCode = $this->_directoryHelper->getBaseCurrencyCode();
            $currency            = $this->_currencyFactory->create()->load($defaultCurrencyCode);

            return $currency->getCurrencySymbol();
        }
        $website = $this->_websiteFactory->create()->load($websiteId, 'website_id');

        return $website->getBaseCurrency()->getCurrencySymbol();
    }
}
