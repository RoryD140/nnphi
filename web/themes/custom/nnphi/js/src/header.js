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
          // If the user account menu is hidden (small screen sizes), prepend it to menu and show it
          if($('#block-useraccountmenu-2:visible').length === 0) {
            $('#block-useraccountmenu-2').prependTo($('.main-nav')).show();
          }

          // Toggles both menu buttons
          $('.menu-btn').toggleClass('menu-btn-open');
          // Toggles menu
          $('#js-main-nav').toggleClass('main-nav-open');
        });
      });
    }
  };
}(jQuery, Drupal));