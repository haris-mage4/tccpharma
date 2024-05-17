define([
    'Magento_Ui/js/form/element/abstract',
    'Amasty_QuoteAttributesManagement/js/components/visible-on-option/strategy'
], function (Element, strategy) {
    'use strict';

    return Element.extend(strategy);
});
