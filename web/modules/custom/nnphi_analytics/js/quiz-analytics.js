if(typeof ga !== 'undefined') {
  (function ($, Drupal, ga) {
    'use strict';

    Drupal.behaviors.quizAnalytics = {
      attach: function (context, settings) {

        // Track all quiz views
        $('#random-quiz', context).once('quiz-view').each(function () {
          ga('send', {
            hitType: 'event',
            eventCategory: 'Quiz View',
            eventAction: 'view',
            eventLabel: 'Quiz View'
          });
        });

        // Track quiz answers
        $('#random-quiz .result-message', context).once('quiz-answer').each(function () {

          var answer;
          // Labels used for google analytics dashboard
          if ($(this).hasClass('quiz-incorrect-answer')) {
            answer = 'incorrect';
          } else {
            answer = 'correct';
          }
          ga('send', {
            hitType: 'event',
            eventCategory: 'Quiz Answer',
            eventAction: 'submit',
            eventLabel: answer
          });
        });
      }
    };
  })(jQuery, Drupal, ga);
}