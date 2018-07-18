// Check to make sure that google analytics object exists — it doesn't if you're logged in
if(typeof ga !== 'undefined') {
  (function ($, Drupal, ga) {
    'use strict';

    Drupal.behaviors.webformAnalytics = {
      attach: function (context, settings) {
        $('.webform-submission-form .js-form-submit', context).once('contact-us').click(function () {
          ga('send', {
            hitType: 'event',
            eventCategory: 'Contact Form Submission',
            eventAction: 'click',
            eventLabel: $('h1').html()
          });
        });
      }
    };
  })(jQuery, Drupal, ga);
}