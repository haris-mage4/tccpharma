/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'Ulmod_Ordernotes/js/order/ordercomment-assigner'
], function ($, wrapper, ordercommentAssigner) {
    'use strict';

    return function (placeOrderAction) {

        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer, paymentData) {
            ordercommentAssigner(paymentData);

            return originalAction(messageContainer, paymentData);
        });
    };
});