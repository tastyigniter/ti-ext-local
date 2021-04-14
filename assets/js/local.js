/* ========================================================================
 * TastyIgniter: local.js v2.2.0
 * https://tastyigniter.com/docs/javascript
 * ======================================================================== */

+function ($) {
    "use strict"

    $(document).on('click', '[data-control="search-local"]', function () {
        $(this).closest('form').submit()
    })

    $(document).on('change', '[data-control="order-type-toggle"] input[type="radio"]', function (event) {
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

}(jQuery)
