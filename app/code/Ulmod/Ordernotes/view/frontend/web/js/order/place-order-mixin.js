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

        /** Override default place order action and add comments to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            ordercommentAssigner(paymentData);

            return originalAction(paymentData, messageContainer);
        });
    };
});