define([
    'jquery',
    'uiRegistry'
], function ($, uiRegistry) {
    $('body').on('validateRequestQuoteForm', function (event, data) {
        var quoteAttributesForm = uiRegistry.get('details.quote-attributes');

         if (!quoteAttributesForm.validate()) {
             data.isValid = false;
             quoteAttributesForm.focusInvalid();
         }
    });
});
