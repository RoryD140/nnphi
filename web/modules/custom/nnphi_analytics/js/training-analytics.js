(function ($, Drupal, ga) {
  'use strict';

  Drupal.behaviors.trainingAnalytics = {
    attach: function (context, settings) {

      // Track clicks on "start learning" on trainings
      $('.training-hero-btn a').once('training-hero-click').click(function(){
        // These links currently open in new tabs, so we don't have to preventDefault to ensure this goes through
        ga('send', {
          hitType: 'event',
          eventCategory: 'Start Learning Click',
          eventAction: 'click',
          eventLabel: $('h1 span').html(),
        });
      });
    }
  };
})(jQuery, Drupal, ga);


