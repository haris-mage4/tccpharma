<?php

namespace Amasty\RequestQuote\Plugin\Model\Quote;

class ItemPlugin
{
    public function afterRepresentProduct(\Magento\Quote\Model\Quote\Item $subject, $result)
    {
        return false;
    }
}
