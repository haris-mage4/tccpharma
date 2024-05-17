<?php
namespace Stathmos\Customize\Preference;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magemonkeys\Quote\Helper\Data;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\ObjectManagerInterface;

class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    protected $directory;
    protected $metadataProvider;
    protected $pageSize = null;
    protected $filter;
    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;
    /**
     * @var GroupRepositoryInterface
     */
    protected GroupRepositoryInterface $groupRepository;
    /**
     * @var Data
     */
    protected Data $mageHelper;
    /**
     * @var Config
     */
    protected Config $eavConfig;
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var Item
     */
    private Item $item;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param GroupRepositoryInterface $groupRepository
     * @param Data $mageHelper
     * @param ObjectManagerInterface $objectManager
     * @param Config $eavConfig
     * @param Product $product
     * @param Item $item
     * @param $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        GroupRepositoryInterface $groupRepository,
        Data $mageHelper,
        ObjectManagerInterface $objectManager,
        Config $eavConfig,
        Product $product,
        Item $item,
        $pageSize = 200
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->pageSize = $pageSize;
        $this->objectManager = $objectManager;
        $this->groupRepository = $groupRepository;
        $this->mageHelper = $mageHelper;
        $this->eavConfig = $eavConfig;
        parent::__construct($filesystem, $filter, $metadataProvider);
        $this->product = $product;
        $this->item = $item;
    }

    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $name = md5(microtime());
        $file = 'export/' . $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        if ($component->getName() == 'amasty_quote_grid') {
            $headers = [
                'ID',
                'Purchase Point',
                'Quote Date',
                'Customer Name',
                'Customer Email',
                'Customer Group',
                'Grand Total',
                'Grand Total (Base)',
                'Expiry Date',
                'Select Your Sales Rep',
                'Facility Name',
                'Additional Feedback on Need',
                'Items',
            ];

            $this->directory->create('export');
            $stream = $this->directory->openFile($file, 'w+');
            $stream->lock();
            $stream->writeCsv($headers);
            $i = 1;
            $searchCriteria = $dataProvider->getSearchCriteria()
                ->setCurrentPage($i)
                ->setPageSize($this->pageSize);
            $totalCount = $dataProvider->getSearchResult()->getTotalCount();
            while ($totalCount > 0) {
                $items = $dataProvider->getSearchResult()->getItems();
                foreach ($items as $item) {
                    if ($component->getName() == 'amasty_quote_grid') {
                        $data = json_decode($item['additional_data'], true);
                        $requestedCustomPrice = '';
                        if ($data !== null) {
                            $requestedCustomPrice = @$data['requested_custom_price'];
                        }

                        $groupRepository = $this->groupRepository;
                        $mageHelper = $this->mageHelper;
                        $uom = $mageHelper->getProductSize($item->getSku());

                        $perQuoteItems = $this->item->getCollection()
                            ->addFieldToFilter('quote_id', $item->getEntityId());

                        $additionalFeedback = '';
                        $salesRep = '';
                        if($item->getAdditionalFeedback() !== null ){
                            $attributeAddFeedback = $this->eavConfig->getAttribute('amasty_quote', 'additional_feedback');
                            $additionalFeedback = $attributeAddFeedback->getSource()->getOptionText($item->getAdditionalFeedback());
                        }

                        if($item->getSelectSalesRep() !== null ){
                            $attributeSalesRep = $this->eavConfig->getAttribute('amasty_quote', 'select_sales_rep');
                            $salesRep = $attributeSalesRep->getSource()->getOptionText($item->getSelectSalesRep());
                        }



                        $groupName = '';
                        if ($item->getCustomerGroupId()) {
                            try {
                                $group = $groupRepository->getById($item->getCustomerGroupId());
                                $groupName = $group->getCode();
                            } catch (NoSuchEntityException $e) {
                                $groupName = 'Group Not Found';
                            }
                        }

                        $product = $this->product->load($item->getProductId());

                        $additionalColumnData = '';
                        $itemNumber = 1;

                        foreach ($perQuoteItems as $quoteItems) {
                            $approvalStatus = $quoteItems->getApprovalStatus() == 1 ? "Approved" : "Pending";

                            $additionalColumnData .= "Item Name: " . $quoteItems->getName() . "\n";
                            $additionalColumnData .= "Sku: " . $quoteItems->getSku() . "\n";
                            $additionalColumnData .= "NDC: " . $item->getNdc() . "\n";
                            $additionalColumnData .= "UOM: " . $uom . "\n";
                            $additionalColumnData .= "Requested QTY: " . $quoteItems->getQty() . "\n";
                            $additionalColumnData .= "Available Qty: " . $quoteItems->getAvailableQty() . "\n";
                            $additionalColumnData .= "List Price: " . $product->getPrice() . "\n";
                            $additionalColumnData .= "Requested Price: " . $requestedCustomPrice . "\n";
                            $additionalColumnData .= "Approved Price: " . $quoteItems->getPrice() . "\n";
                            $additionalColumnData .= "Approval Status: " . $approvalStatus . "\n";
                            $additionalColumnData .= "Quote Note: " . $quoteItems->getQuoteNote() . "\n";
                            $additionalColumnData .= "S. NO.: " . $itemNumber . "\n\n";

                            $itemNumber++;
                        }

                        $additionalColumnData = rtrim($additionalColumnData, "\n\n");



                        $itemData = [];

                        $itemData[] = $item['increment_id'];
                        $itemData[] = 'Default Store View';
                        $itemData[] = $item->getSubmitedDate();
                        $itemData[] = $item->getCustomerName();
                        $itemData[] = $item->getCustomerEmail();
                        $itemData[] = $groupName;
                        $itemData[] = $item->getgGandTotal();
                        $itemData[] = $item->getBaseGrandTotal();
                        $itemData[] = $item->getExpiredDate();
                        $itemData[] = $salesRep;
                        $itemData[] = $item->getFacility();
                        $itemData[] = $additionalFeedback;
                        $additionalColumnDataArray = explode("\n\n", $additionalColumnData);
                        foreach ($additionalColumnDataArray as $data) {
                            $itemData[] = $data;
                        }
                    }
                    $stream->writeCsv($itemData);
                }
                $searchCriteria->setCurrentPage(++$i);
                $totalCount = $totalCount - $this->pageSize;
            }
            $stream->unlock();
            $stream->close();

            return [
                'type' => 'filename',
                'value' => $file,
                'rm' => true
            ];
        }
    }
}
