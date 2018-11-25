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

}(jQuery)
