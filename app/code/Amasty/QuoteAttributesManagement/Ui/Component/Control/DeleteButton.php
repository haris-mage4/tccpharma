<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Ui\Component\Control;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        Escaper $escaper
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        $attributeId = (int) $this->request->getParam('attribute_id');
        if ($attributeId) {
            $url = $this->urlBuilder->getUrl('*/*/delete');
            $escapedMessage = $this->escaper->escapeHtml(__('Are you sure you want to delete this field?'));
            $data = [
                'label' => __('Delete Field'),
                'class' => 'delete',
                'on_click' => sprintf(
                    'deleteConfirm("%s", "%s", {data:{"id":%d}})',
                    $escapedMessage,
                    $url,
                    $attributeId
                ),
                'sort_order' => 30,
            ];
        }
        return $data;
    }
}
