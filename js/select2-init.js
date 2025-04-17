jQuery(document).ready(function($) {
    $('.select2-products').select2({
        ajax: {
            url: jmb_ajax_object.ajax_url, // URL for the AJAX request
            dataType: 'json',
            delay: 250, // Delay in milliseconds to reduce server load
            data: function (params) {
                console.log(params);
                return {
                    action: 'GetOrderDataAction', // The AJAX action
                    search: params.term // The search term
                };
            },
            processResults: function (data) {
                console.log(data);
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 3, // Minimum characters to start searching
        placeholder: 'Search for a fields',
        allowClear: true
    });
    
});