/**
 *
 * @type {{attach: Drupal.behaviors.nnphiHeader.attach}}
 */

(function ($, Drupal) {
  'use strict'

  Drupal.behaviors.nnphiHeader = {
    attach: function (context) {

      $(context).find('.menu-btn').once('nnphi-header').each(function() {
        this.addEventListener('click', function() {
          // Toggles both menu buttons
          $('.menu-btn').toggleClass('menu-btn-open');
          // Toggles menu
          $('#js-main-nav').toggleClass('main-nav-open');
        });
      });
    }
  };
}(jQuery, Drupal));