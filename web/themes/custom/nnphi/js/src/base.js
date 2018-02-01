/**
 * @file
 * Custom scripts for theme.
 */

(function ($, Drupal) {
    'use strict'

    Drupal.behaviors.nnphiStars = {
        attach: function (context) {
            $.fn.stars = function() {
                return $(this).each(function() {
                    // Get the value
                    var val = parseFloat($(this).html());
                    val = Math.round(val * 2) / 2; /* To round to nearest half */
                    // Make sure that the value is in 0 - 5 range, multiply to get width
                    var size = Math.max(0, (Math.min(5, val))) * 16;
                    // Create stars holder
                    var $span = $('<span />').width(size);
                    // Replace the numerical value with stars
                    $(this).html($span);
                });
            };

            $(context).once('body').each(function() {
                $('span.stars').stars();
            });

        }
    };
}(jQuery, Drupal));