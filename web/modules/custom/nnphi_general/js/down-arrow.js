(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.downArrow = {
    attach: function (context, settings) {
      // scroll to next section when arrow is clicked
      $('.down-arrow-scroll svg', context).once('down-arrow').each(function () {
        $(this).click(function () {
          // Look for the parent block element
          var nextEl = $(this).closest(".block").next();
          if(nextEl.length === 0) {
            // If block is only block in region, go to the next region
            nextEl = $(this).closest(".layout__region").next();
          }
          $('html, body').animate({
            scrollTop: $(nextEl).offset().top
          }, 500);
        });
      });
    }
  };
})(jQuery, Drupal);


