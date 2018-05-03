(function ($, Drupal) {
  'use strict'
  Drupal.behaviors.randomQuiz = {
    attach: function (context, settings) {
      $('div.random-quiz.js-pre-ajax', context).once('random-quiz').each(function () {

        // Prevents a new quiz from being ajaxed in every time ajax runs on the page
        // Mainly prevents duplicate blocks from displaying when editing these on a panels page
        $(this).removeClass('js-pre-ajax');

        // Get a random quiz.
        Drupal.ajax({
          url: Drupal.url('assessment/random'),
          submit: {selector: $(this).attr('id')}
        }).execute();
      });
    }
  };
})(jQuery, Drupal);
