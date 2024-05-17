var config = {
    shim: {
        'Amasty_RequestQuote/js/quote/submit': {
            deps: [
                'Amasty_QuoteAttributes/js/quote/submit/add-validation',
                'Amasty_QuoteAttributes/js/quote/submit/rewrite-data-from-provider'
            ]
        }
    }
};
