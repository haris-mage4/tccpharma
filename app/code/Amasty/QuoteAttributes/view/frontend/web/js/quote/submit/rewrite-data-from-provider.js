define([
    'jquery',
    'underscore',
    'uiRegistry'
], function ($, _, uiRegistry) {
    $('body').on('beforeSubmitRequestQuoteForm', function (event, data) {
        var form = data.form,
            quoteAttributesProvider = uiRegistry.get('details.quote-attributes-provider');

        $.each(quoteAttributesProvider.get('data.quote_entity'), function (name, value) {
            // TODO: update algorithm if update ui structure with nested dataScope
            form.find('[name="quote_entity[' + name + ']"]').val(value);
        });
    });
});
