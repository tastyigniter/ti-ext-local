/* ========================================================================
 * TastyIgniter: local.js v2.2.0
 * https://tastyigniter.com/docs/javascript
 * ======================================================================== */

+function ($) {
    "use strict"

    $(document)
        .on('click', '[data-control="search-local"]', function () {
            $(this).closest('form').submit()
        })
        .on('change', '[data-control="order-type-toggle"] input[type="radio"]', function (event) {
            var $input = $(event.currentTarget),
                $el = $input.closest('[data-control="order-type-toggle"]')

            $el.find('input[type="radio"]').attr('disabled', true)
            $el.find('.btn').addClass('disabled')
            $.request($el.data('handler'), {
                data: {'type': $input.val()}
            }).always(function () {
                $el.find('input[type="radio"]').attr('disabled', false)
                $el.find('.btn').removeClass('disabled')
            })
        })
        .on('click', '[data-address-picker-control="new"]', function () {
            $('#local-search-form').toggleClass('hide')
            $('[data-control="address-picker"]').toggleClass('hide')
            $('#local-search-form #search-query').focus()
        })
        .on('ajaxSetup', '[data-address-picker-control="select"]', function () {
            $('[data-control="address-picker"] [data-toggle="dropdown"]').addClass('disabled')
            $('[data-address-picker-loading]').addClass('fa-spinner fa-spin')
        })
        .on('ajaxDone', '[data-address-picker-control="select"]', function () {
            $('[data-control="address-picker"] [data-toggle="dropdown"]').removeClass('disabled')
            $('[data-address-picker-loading]').removeClass('fa-spinner fa-spin')
        })
        .on('ajaxFail', '[data-address-picker-control="select"]', function () {
            $('[data-control="address-picker"] [data-toggle="dropdown"]').removeClass('disabled')
            $('[data-address-picker-loading]').removeClass('fa-spinner fa-spin')
        })

    $(document).on('change', 'input[type="radio"][name="order_type"][data-page-url]', function (event) {
        var $input = $(event.currentTarget),
            pageUrl = $input.data('pageUrl')

        window.location.href = pageUrl+'order_type='+$input.val()
    })

}(jQuery)
