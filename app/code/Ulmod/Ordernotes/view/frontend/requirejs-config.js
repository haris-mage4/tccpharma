var config = {
config: {
    mixins: {
        'Magento_Checkout/js/action/place-order': {
            'Ulmod_Ordernotes/js/order/place-order-mixin': true
        },
        'Magento_Checkout/js/action/set-payment-information': {
            'Ulmod_Ordernotes/js/order/set-payment-information-mixin': true
        }
    }
}
};