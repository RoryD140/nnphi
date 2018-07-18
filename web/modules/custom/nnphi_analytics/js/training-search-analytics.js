// Check to make sure that google analytics object exists — it doesn't if you're logged in
if(typeof ga !== 'undefined') {
  (function ($, Drupal, ga) {
    'use strict';

    Drupal.behaviors.trainingSearchAnalytics = {
      attach: function (context, settings) {
        $('.training-search-form').once('training-search').submit(function () {
          ga('send', {
            hitType: 'event',
            eventCategory: 'Search Term Entered',
            eventAction: 'click',
            eventLabel: $('.training-search-input', this).val()
          });
        });
      }
    };
  })(jQuery, Drupal, ga);

}
