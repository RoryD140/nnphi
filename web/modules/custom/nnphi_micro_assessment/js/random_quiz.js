(function ($, Drupal, debounce) {
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

  Drupal.behaviors.quizSlidein = {
    attach: function (context, settings) {
      $('div.block-random-quiz', context).once('quiz-slide-in').each(function () {

        var that = $(this);

        var slideIn = debounce(function() {
          // Set timeout in case we're reloading a page that's already scrolled; keeps it from popping up immediately
          setTimeout(function(){
            $(that).addClass('active');
          }, 1000);
        }, 20);

        $(window).scroll(function() {

          // Conditions
          // ensure we've scrolled halfof the available scrolling distance
          var scrollHalf = $(document).scrollTop() >= ($(document).height() - $(window).height())/2,
              noCookie = !$.cookie('hide-quiz'),
              notDisabled = !$(that).hasClass('disabled'); // Ensures that this doesn't keep popping up on same page if cookies are disabled

          if(scrollHalf && noCookie && notDisabled) {
            slideIn();
          }
        });


        $('.micro-assessment-close-btn', that).click(function(){
          $(that).removeClass('active').addClass('disabled');
          $.cookie('hide-quiz', true);
        });
      });
    }
  };
})(jQuery, Drupal, Drupal.debounce);
