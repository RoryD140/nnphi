(function ($, Drupal) {
  'use strict'
  Drupal.behaviors.randomQuiz = {
    attach: function (context, settings) {
      $('div.random-quiz', context).once('random-quiz').each(function () {
        // Get a random quiz.
        Drupal.ajax({
          url: Drupal.url('assessment/random'),
          submit: {selector: $(this).attr('id')}
        }).execute();
      });
    }
  };
})(jQuery, Drupal);
