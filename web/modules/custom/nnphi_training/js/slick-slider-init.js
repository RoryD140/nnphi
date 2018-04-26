/**
 * Initialize Slick Slider
 * Options available at http://kenwheeler.github.io/slick/
 */

(function ($, Drupal) {
  Drupal.behaviors.slickSlider = {
    attach: function (context, settings) {

      $(context).find('.slick-slider').once('slick').slick({
        mobileFirst: true,
        slidesToShow: 1,
        infinite: true,
        centerPadding: '40px',
        // Att bootstrap's medium breakpoint, add centermode and variable width;
        responsive: [
          {
            breakpoint: 768,
            settings  : {
              centerMode: true,
              variableWidth: true,
            }
          }
        ]
        }
      );

      $(context).find('.training-vert-slider').once('vertical-slick').slick({
        slidesToShow: 3,
        vertical: true,
        infinite: false
      });
    }
  };
})(jQuery, Drupal);

