(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.downArrow = {
    attach: function (context, settings) {
      // scroll to next section when arrow is clicked
      $('.down-arrow-scroll svg', context).once('down-arrow').each(function () {
        $(this).click(function () {
          var nextEl = $(this).closest('.search-training-block').next();
          $('html, body').animate({
            scrollTop: $(nextEl).offset().top
          }, 500);
        });
      });
    }
  };
})(jQuery, Drupal);


