<?php
namespace Stathmos\Customize\Plugin\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;

class CustomBuilder {

    public function afterBuild(Builder $subject, $result)
    {
        $likeToken = $this->getLikeTokenizer();
        $result['analysis']['tokenizer'] = $likeToken;
        $result['analysis']['filter']['trigrams_filter'] = [
            'type' => 'ngram',
            'min_gram' => 3,
            'max_gram' => 3
        ];
        $result['analysis']['analyzer']['my_analyzer'] = [
            'type' => 'custom',
            'tokenizer' => 'standard',
            'filter' => [
                'lowercase', 'trigrams_filter'
            ]
        ];
        return $result;
    }


    protected function getLikeTokenizer(): array
    {
        return [
            'default_tokenizer' => [
                'type' => 'ngram'
            ],
        ];
    }
}
