<?php

namespace Magemonkeys\Customerinfo\Helper;

use Amasty\RequestQuote\Model\QuoteFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Amasty\QuoteAttributes\Model\QuoteEntityFactory;
use Magento\Framework\App\Helper\Context;
use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;

class Data extends AbstractHelper
{
    protected QuoteFactory $quoteFactory;
    protected QuoteEntityFactory $quoteEntityFactory;
    protected QuoteEntityRepositoryInterface $quoteEntityRepository;

    public function __construct(
        QuoteFactory $quoteFactory,
        Context $context,
        QuoteEntityFactory $quoteEntityFactory,
        QuoteEntityRepositoryInterface $quoteEntityRepository
    ) {
        parent::__construct($context);
        $this->quoteFactory = $quoteFactory;
        $this->quoteEntityFactory = $quoteEntityFactory;
        $this->quoteEntityRepository = $quoteEntityRepository;
    }

    public function getAdditionalData($customerId)
    {
        $attributeHtml = '';
        $salesRepForAllQuote = '';
        $facilityName = '';

        try {
            $quote = $this->quoteFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter('status', ['eq' => 1])
                ->getLastItem();

            $quoteId = $quote->getId();

            if(isset($quoteId)){
                $quoteEntityRepository = $this->quoteEntityRepository->getByQuoteId($quoteId);
                $model = $this->quoteEntityFactory->create()->load($quoteEntityRepository->getEntityId());
                $quoteAttributeData = $model->getData();

                unset($quoteAttributeData['entity_id']);
                unset($quoteAttributeData['quote_id']);

                foreach ($quoteAttributeData as $key => $value) {
                    $attributeCode = $key; // Replace with the actual attribute code
                    $attributeValue = $value;
                    $attributeOptions = $model->getResource()->getAttribute($attributeCode)->getSource()->getAllOptions();
                    $attribute = $model->getResource()->getAttribute($attributeCode);
                    $attributeLabel = $attribute->getDefaultFrontendLabel();

                    if ($attributeCode == 'select_sales_rep') {
                        foreach ($attributeOptions as $option) {
                            if ($option['value'] == $attributeValue) {
                                $attributeHtml .= "<span><b>" . $attribute->getDefaultFrontendLabel() . "</b></span> : " . $option['label'] . "<br>";
                                $salesRepForAllQuote = "<span><b>" . $attribute->getDefaultFrontendLabel() . "</b></span> : " . $option['label'] . "<br>";
                                break;
                            }
                        }
                    } elseif ($attributeCode == 'target_price') {
                        if (!$attributeValue) {
                            $attributeHtml .= "<br><span><b>" . $attributeLabel . "</b></span> : No <br>";
                        } else {
                            $attributeHtml .= "<br><span><b>" . $attributeLabel . "</b></span> : Yes <br>";
                        }
                    } else {
                        if ($attribute) {
                            $attributeLabel = $attribute->getDefaultFrontendLabel();
                            $attributeHtml .= "<br><span><b>" . $attributeLabel . "</b></span> : " . $attributeValue . "<br>";
                        }
                    }

                    if ($attributeCode == 'facility') {
                        $facilityLabel = $attribute->getDefaultFrontendLabel();
                        $facilityName = $facilityLabel . ' : ' . $attributeValue;
                    }
                }

                return [
                    'all_attribute_data' => $attributeHtml,
                    'facility_name' => $facilityName,
                    'select_sales_rep' => $salesRepForAllQuote,
                ];
            }

        } catch (\Exception $e) {
            return 'No Data Found';
        }
    }
}
