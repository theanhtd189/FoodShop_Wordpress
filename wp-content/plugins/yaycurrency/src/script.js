'use strict';
(function ($) {
  const yay_currency = () => {
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.href);
    }

    if ($('.yay-currency-dropdown').length > 0) {
      $('.yay-currency-dropdown')
        .css({ 'background-color': 'transparent' })
        .html(yayCurrency.shortCode);
    }
  };

  jQuery(document).ready(function ($) {
    yay_currency($);
    const { yayCurrency } = window;

    $(document.body).on('update_checkout', function (e) {
      e.stopImmediatePropagation();
    });

    $(document.body).trigger('wc_fragment_refresh');

    if (
      typeof wc_cart_fragments_params === 'undefined' ||
      wc_cart_fragments_params === null
    ) {
    } else {
      sessionStorage.removeItem(wc_cart_fragments_params.fragment_name);
    }

    const switcherUpwards = function () {
      const allSwitcher = $('.yay-currency-single-page-switcher');

      allSwitcher.each(function () {
        const SWITCHER_LIST_HEIGT = 250;

        const offsetTop =
          $(this).offset().top + $(this).height() - $(window).scrollTop();
        const offsetBottom =
          $(window).height() -
          $(this).height() -
          $(this).offset().top +
          $(window).scrollTop();

        if (
          offsetBottom < SWITCHER_LIST_HEIGT &&
          offsetTop > SWITCHER_LIST_HEIGT
        ) {
          $(this).find('.yay-currency-custom-options').addClass('upwards');
          $(this).find('.yay-currency-custom-arrow').addClass('upwards');
          $(this)
            .find('.yay-currency-custom-select__trigger')
            .addClass('upwards');
        } else {
          $(this).find('.yay-currency-custom-options').removeClass('upwards');
          $(this).find('.yay-currency-custom-arrow').removeClass('upwards');
          $(this)
            .find('.yay-currency-custom-select__trigger')
            .removeClass('upwards');
        }
      });
    };
    $(window).on('load resize scroll', switcherUpwards);

    $('.yay-currency-custom-select-wrapper').on('click', function () {
      $('.yay-currency-custom-select', this).toggleClass('open');
      $('#slide-out-widget-area')
        .find('.yay-currency-custom-options')
        .toggleClass('overflow-fix');
      $('[id^=footer]').toggleClass('z-index-fix');
      $('.yay-currency-custom-select', this)
        .parents('.handheld-navigation')
        .toggleClass('overflow-fix');
    });

    $('.yay-currency-custom-option-row').each(function () {
      $(this).on('click', function () {
        const currencyID = $(this).data('value');
        const countryCode = $(this)
          .children('.yay-currency-flag')
          .data('country_code');
        $('.yay-currency-switcher').val(currencyID).change();
        if (!$(this).hasClass('selected')) {
          $(this)
            .parent()
            .find('.yay-currency-custom-option-row.selected')
            .removeClass('selected');
          $(this).addClass('selected');
          $(this)
            .closest('.yay-currency-custom-select')
            .find('.yay-currency-flag.selected')
            .css({
              background: `url(${yayCurrency.yayCurrencyPluginURL}assets/dist/flags/${countryCode}.svg)`,
            });
          $(this)
            .closest('.yay-currency-custom-select')
            .find(
              '.yay-currency-custom-select__trigger .yay-currency-selected-option'
            )
            .text($(this).text());
        }
      });
    });

    window.addEventListener('click', function (e) {
      const selects = document.querySelectorAll('.yay-currency-custom-select');
      selects.forEach((select) => {
        if (!select.contains(e.target)) {
          select.classList.remove('open');
        }
      });
    });
  });
})(jQuery);
