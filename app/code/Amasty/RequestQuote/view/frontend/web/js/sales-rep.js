require([
    'jquery'
], function($) {
    $(document).ready(function() {
        $('body').on('change','select[name="quote_entity[select_sales_rep]"]',function() {
        var quoteSalesRep = $(this).val();
        if (quoteSalesRep === '13') {
            var customerSalesRep = 32;
        }
        else if (quoteSalesRep === '14') {
            var customerSalesRep = 28;
        }
        else if (quoteSalesRep === '15') {
            var customerSalesRep = 29;
        }
        else if (quoteSalesRep === '16') {
            var customerSalesRep = 35;
        }
        else if (quoteSalesRep === '17') {
            var customerSalesRep = 27;
        }
        else if (quoteSalesRep === '19') {
            var customerSalesRep = 40;
        }
        else if (quoteSalesRep === '18') {
            var customerSalesRep = 41;
        }
        else {
            var customerSalesRep = 41;
        }
        // Set the selected value in the second dropdown field
        $('select[name="sales_representative"]').val(customerSalesRep);
        });
    });
    $('body').on('keyup','input[name="quote_entity[facility]"]',function() {
        var facility = $(this).val();
        console.log(facility);
        $('input[name="facility_name"]').val(facility);
    });
});
