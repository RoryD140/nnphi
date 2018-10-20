if(typeof ga !== 'undefined') {
  (function ($, Drupal, ga) {
    'use strict';

    Drupal.behaviors.personalizeAnalytics = {
      attach: function (context, settings) {

        // Track all clicks on personalize your learning experience on front page
        $('.field-background-block-link a', context).once('personalize').each(function () {
          $(this).click(function(){
            ga('send', {
              hitType: 'event',
              eventCategory: 'Personalize Your Learning Experience',
              eventAction: 'click',
              eventLabel: 'Personalize Your Learning Experience'
            });
          });
        });
      }
    };
  })(jQuery, Drupal, ga);
}
