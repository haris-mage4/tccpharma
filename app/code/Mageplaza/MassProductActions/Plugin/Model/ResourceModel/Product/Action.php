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

namespace Mageplaza\MassProductActions\Plugin\Model\ResourceModel\Product;

use Closure;
use Exception;
use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\MassProductActions\Helper\Data as HelperData;
use Mageplaza\MassProductActions\Model\Config\Source\Calculation;
use Mageplaza\MassProductActions\Model\Config\Source\MultiSelectFilter;
use Mageplaza\MassProductActions\Model\Config\Source\TextFilter;

/**
 * Class MassAction
 * @package Mageplaza\MassProductActions\Plugin\Component
 */
class Action extends ProductAction
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var ProductResource
     */
    protected $_productResource;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @param ProductAction $productAction
     * @param Closure $proceed
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     *
     * @return Action
     * @throws Exception
     * @SuppressWarnings(Unused)
     */
    public function aroundUpdateAttributes(
        ProductAction $productAction,
        Closure $proceed,
        $entityIds,
        $attrData,
        $storeId
    ) {
        $objectManager          = ObjectManager::getInstance();
        $this->_helperData      = $objectManager->create(HelperData::class);
        $this->_request         = $objectManager->get(RequestInterface::class);
        $this->_coreRegistry    = $objectManager->get(Registry::class);
        $this->_productResource = $objectManager->create(ProductResource::class);

        if (!$this->_helperData->isEnabled()
            || ($this->_request->getFullActionName() !== 'mpmassproductactions_product_massAttribute'
                && $this->_request->getFullActionName() === 'mpmassproductactions_product_massPrice')
        ) {
            $proceed($entityIds, $attrData, $storeId);
        } else {
            $attributesFilter = $this->_coreRegistry->registry('mp_massproductactions_attributes_filter');
            $priceFilter      = $this->_coreRegistry->registry('mp_massproductactions_price_filter');

            /** @var AbstractModel $object */
            $object = new DataObject();
            $object->setStoreId($storeId);

            $this->getConnection()->beginTransaction();
            try {
                foreach ($attrData as $attrCode => $newValue) {
                    $attribute = $this->getAttribute($attrCode);
                    if (!$attribute->getAttributeId()) {
                        continue;
                    }
                    $i = 0;
                    foreach ($entityIds as $entityId) {
                        $value = $newValue;
                        $i++;
                        $object->setId($entityId);
                        $object->setEntityId($entityId);
                        $oldValue = $this->_productResource->getAttributeRawValue(
                            $entityId,
                            $attrCode,
                            $storeId
                        );

                        if (isset($attributesFilter[$attrCode]) && $oldValue) {
                            $value = $this->attributeFilterCalculation($attributesFilter[$attrCode], $oldValue, $value);
                        }

                        if ($attrCode === 'custom_design_from' && !isset($attrData['custom_design_to'])) {
                            $this->validateDate($entityId, 'custom_design_to', $storeId, $value);
                        }
                        if ($attrCode === 'custom_design_to' && !isset($attrData['custom_design_from'])) {
                            $this->validateDate($entityId, 'custom_design_from', $storeId, $value);
                        }
                        if ($attrCode === 'news_from_date' && !isset($attrData['news_to_date'])) {
                            $this->validateDate($entityId, 'news_to_date', $storeId, $value);
                        }
                        if ($attrCode === 'news_to_date' && !isset($attrData['news_from_date'])) {
                            $this->validateDate($entityId, 'news_from_date', $storeId, $value);
                        }
                        if (isset($priceFilter[$attrCode]) && $priceFilter[$attrCode]['type'] !== '0') {
                            if (isset($priceFilter[$attrCode]['using_cost']) && $priceFilter[$attrCode]['using_cost']) {
                                $oldValue = $this->_productResource->getAttributeRawValue(
                                    $entityId,
                                    'cost',
                                    $storeId
                                );
                            }
                            if (isset($priceFilter[$attrCode]['using_price'])
                                && $priceFilter[$attrCode]['using_price']
                            ) {
                                $oldValue = $this->_productResource->getAttributeRawValue(
                                    $entityId,
                                    'price',
                                    $storeId
                                );
                            }
                            $value = $this->priceFilterCalculation($priceFilter[$attrCode]['type'], $oldValue, $value);
                        }

                        // collect data for save
                        $this->_saveAttributeValue($object, $attribute, $value);
                        // save collected data every 1000 rows
                        if ($i % 1000 === 0) {
                            $this->_processAttributeValues();
                        }
                    }
                    $this->_processAttributeValues();
                }

                $this->getConnection()->commit();
            } catch (Exception $e) {
                $this->getConnection()->rollBack();
                throw $e;
            }
        }

        return $this;
    }

    /**
     * @param int $entityId
     * @param string $key
     * @param int $storeId
     * @param string $value
     *
     * @throws LocalizedException
     */
    public function validateDate($entityId, $key, $storeId, $value)
    {
        $maxValue = $this->_productResource->getAttributeRawValue(
            $entityId,
            $key,
            $storeId
        );

        if (strpos($key, 'to') !== false && !is_array($maxValue) && !$maxValue) {
            if (strtotime($value) >= strtotime($maxValue)) {
                throw new LocalizedException(
                    __('Make sure the To Date is later than or the same as the From Date.')
                );
            }
        }

        if (strpos($key, 'from') !== false) {
            if (is_array($maxValue) || !$maxValue) {
                $maxValue = date('m/d/Y');
            }

            if (strtotime($value) <= strtotime($maxValue)) {
                throw new LocalizedException(
                    __('Make sure the To Date is later than or the same as the From Date.')
                );
            }
        }
    }

    /**
     * @param string $type
     * @param string $oldValue
     * @param string $newValue
     *
     * @return false|mixed|string|string[]|null
     */
    public function attributeFilterCalculation($type, $oldValue, $newValue)
    {
        switch ($type) {
            case TextFilter::UPPER_CASE:
                $value = mb_strtoupper($oldValue, 'UTF-8');
                break;

            case TextFilter::LOWER_CASE:
                $value = mb_strtolower($oldValue, 'UTF-8');
                break;

            case TextFilter::CAPITALIZE_EACH_WORD:
                $value = mb_convert_case($oldValue, MB_CASE_TITLE, 'UTF-8');
                break;

            case TextFilter::TOGGLE_CASE:
                $value = $this->toggleCaseConvert(mb_strtoupper($oldValue, 'UTF-8'));
                break;

            case MultiSelectFilter::ADD:
                $oldValueArr = is_array($oldValue) ? $oldValue : explode(',', $oldValue);
                $newValueArr = explode(',', $newValue);
                $valueArr    = array_unique(array_merge($oldValueArr, $newValueArr));
                $value       = implode(',', $valueArr);
                break;

            default:
                $value = $newValue;
        }

        return $value;
    }

    /**
     * @param string $type
     * @param string $oldValue
     * @param string $newValue
     *
     * @return float|int
     */
    public function priceFilterCalculation($type, $oldValue, $newValue)
    {
        switch ($type) {
            case Calculation::PLUS:
                $value = (float) $oldValue + (float) $newValue;

                break;

            case Calculation::PLUS_BY_PERCENT:
                $plusValue = (float) $newValue * (float) $oldValue / 100;
                $value     = (float) $oldValue + $plusValue;

                break;

            case Calculation::MINUS:
                $value = (float) $oldValue - (float) $newValue;
                if ($value < 0) {
                    $value = 0;
                }

                break;

            case Calculation::MINUS_BY_PERCENT:
                $minusValue = (float) $newValue * (float) $oldValue / 100;
                $value      = (float) $oldValue - $minusValue;
                if ($value < 0) {
                    $value = 0;
                }

                break;

            case MultiSelectFilter::ADD:
                $oldValueArr = explode(',', $oldValue);
                $newValueArr = explode(',', $newValue);
                $valueArr    = array_unique(array_merge($oldValueArr, $newValueArr));
                $value       = implode(',', $valueArr);
                break;

            default:
                $value = $newValue;
        }

        return $value;
    }

    /**
     * @param string $str
     * @param string $encoding
     *
     * @return string
     */
    public function toggleCaseConvert($str, $encoding = 'UTF-8')
    {
        $words    = explode(' ', $str);
        $newWords = [];
        foreach ($words as $word) {
            $stringLen = mb_strlen($word, $encoding);
            if ($stringLen === 1) {
                $word = mb_strtolower($word, $encoding);
            } else {
                for ($i = 0; $i < $stringLen; $i++) {
                    if (preg_match("/\w/", $word[$i])) {
                        $word[$i] = mb_strtolower($word[$i], $encoding);

                        break;
                    }
                }
            }

            $newWords[] = $word;
        }

        return $newWords ? implode(' ', $newWords) : $str;
    }
}
