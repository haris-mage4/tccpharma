<?php

namespace Eaglerocket\Customquote\Ui\Component\Listing\Post\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;


class Action extends Column {

    /** Url path */
    const URL_PATH_EDIT = 'eaglerocket_customquote/post/addrow';
    const URL_PATH_DELETE = 'eaglerocket_customquote/post/delete';
    // const URL_PATH_VIEW = 'helloworld/post/view';

    protected $actionUrlBuilder;
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context, 
        UiComponentFactory $uiComponentFactory, 
        UrlInterface $urlBuilder, 
        array $components = [], 
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['post_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_EDIT, [
                                    'id' => $item['post_id']
                                ]
                        ),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_DELETE, [
                                    'id' => $item['post_id']
                                ]
                        ),
                        'label' => __('Delete'),
                        // 'confirm' => [
                        //     'title' => __('Delete "${ $.$data.title }"'),
                        //     'message' => __('Are you sure you wan\'t to delete a "${ $.$item.post_id }" record?')
                        // ]
                    ];
                 
                }
            }
        }

        return $dataSource;
    }

}
