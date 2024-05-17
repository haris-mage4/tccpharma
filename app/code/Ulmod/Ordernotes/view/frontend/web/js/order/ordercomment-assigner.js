/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    /** Override default place order action and add comment to request */
    return function (paymentData) {
	 
        if (paymentData['extension_attributes'] === undefined) {
            paymentData['extension_attributes'] = {};
        }

        paymentData['extension_attributes']['comment'] = jQuery('[name="um_order_comment"]').val();
					
    };
});