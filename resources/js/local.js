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
                data: {'type': $input.val(), 'redirect': $el.data('redirect')}
            }).always(function () {
                $el.find('input[type="radio"]').attr('disabled', false)
                $el.find('.btn').removeClass('disabled')
            })
        })
        .on('click', '[data-control="order-type-toggle"] [data-order-type-code]', function (event) {
            var $btn = $(event.currentTarget),
                $el = $btn.closest('[data-control="order-type-toggle"]')

            $el.find('[data-bs-toggle="dropdown"]').attr('disabled', true)
            $el.find('.dropdown-item').addClass('disabled')
            $.request($el.data('handler'), {
                data: {'type': $btn.data('orderTypeCode'), 'redirect': $el.data('redirect')}
            }).always(function () {
                $el.find('.dropdown-item').removeClass('disabled')
            })
        })
        .on('click', '[data-address-picker-control="new"]', function () {
            $('#local-search-form').toggleClass('hide')
            $('[data-control="address-picker"]').toggleClass('hide')
            $('#local-search-form #search-query').focus()
        })
        .on('ajaxSetup', '[data-address-picker-control="select"]', function () {
            $('[data-control="address-picker"] [data-bs-toggle="dropdown"]').addClass('disabled')
            $('[data-address-picker-loading]').addClass('fa-spinner fa-spin')
        })
        .on('ajaxDone', '[data-address-picker-control="select"]', function () {
            $('[data-control="address-picker"] [data-bs-toggle="dropdown"]').removeClass('disabled')
            $('[data-address-picker-loading]').removeClass('fa-spinner fa-spin')
        })
        .on('ajaxFail', '[data-address-picker-control="select"]', function () {
            $('[data-control="address-picker"] [data-bs-toggle="dropdown"]').removeClass('disabled')
            $('[data-address-picker-loading]').removeClass('fa-spinner fa-spin')
        })

    $(document).on('change', 'input[type="radio"][data-page-url]', function (event) {
        var $input = $(event.currentTarget),
            pageUrl = $input.data('pageUrl')

        window.location.href = pageUrl+$input.attr('name')+'='+$input.val()
    })

    $(document).render(function () {
        setTimeout(function () {
            const $body = $('body'),
                params = new Proxy(new URLSearchParams(window.location.search), {
                    get: (searchParams, prop) => searchParams.get(prop),
                });

            if (!params || !params.menuId) return;

            const $button = $('<button type="button" data-cart-control="load-item" data-menu-id="' + params.menuId + '"></button>');

            $body.prepend($button);
            $button.trigger('click');

            const uri = window.location.href.toString(),
                cleanUri = uri.substring(0, uri.indexOf("?"));

            window.history.replaceState({}, document.title, cleanUri);
        }, 500);
    });
}(jQuery)
