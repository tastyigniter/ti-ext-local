/* ========================================================================
 * TastyIgniter: local.js v2.2.0
 * https://tastyigniter.com/docs/javascript
 * ======================================================================== */

+function ($) {
    "use strict"

    $(document).on('click', '[data-control="search-local"]', function () {
        $(this).closest('form').request(null, {
            error: function (xhr) {
                $.ti.flashMessage({class: 'danger', text: xhr.responseJSON.result})
            }
        })
    })
}(jQuery)
