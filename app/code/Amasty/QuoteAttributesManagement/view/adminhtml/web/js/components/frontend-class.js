define([
    'Magento_Ui/js/form/element/select',
    'Amasty_QuoteAttributesManagement/js/components/visible-on-option/strategy',
    'Amasty_QuoteAttributesManagement/js/components/options-by-type/strategy'
], function (Element, visibleStrategy, optionsByTypeStrategy) {
    'use strict';

    return Element.extend(visibleStrategy).extend(optionsByTypeStrategy);
});
