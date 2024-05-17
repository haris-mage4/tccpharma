define([
    'jquery',
    'Magento_Customer/js/model/customer',
    'mage/tooltip'
], function ($, customer) {
    'use strict';

    return function (config, element) {
        var isLoggedIn = customer.isLoggedIn();
        var tooltipDescription;

        if (isLoggedIn) {
            tooltipDescription = 'Tooltip description for logged-in users.';
        } else {
            tooltipDescription = 'Tooltip description for guest users.';
        }

        // Set the tooltip description
        $(element).tooltip({
            content: tooltipDescription
        });
    };
});
