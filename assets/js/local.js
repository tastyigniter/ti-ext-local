/* ========================================================================
 * TastyIgniter: local.js v2.2.0
 * https://tastyigniter.com/docs/javascript
 * ======================================================================== */

+function ($) {
    "use strict"

    $(document).on('click', '[data-control="search-local"]', function () {
        $(this).closest('form').submit()
    })

    $(document).render(function () {
        var $affixEl = $('.affix-categories'),
            offsetTop = $('.navbar-top').height(),
            offsetBottom = $('footer.footer').outerHeight(true),
            cartWidth = $affixEl.parent().width()

        $affixEl.affix({
            offset: {top: offsetTop, bottom: offsetBottom}
        })

        $affixEl.on('affixed.bs.affix', function () {
            $affixEl.css('width', cartWidth)
        })
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
