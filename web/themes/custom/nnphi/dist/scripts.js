/**
 * @file
 * Custom scripts for theme.
 */

/**
 *
 * @type {{attach: Drupal.behaviors.bcHeader.attach}}
 */
Drupal.behaviors.nnphiHeader = {
  attach: function (context, settings) {

    // Close btn
    const navBtn = context.getElementById('js-menu-btn'),
          navBtnClose = context.getElementById('js-menu-btn-close'),
          mainNav = context.getElementById('js-main-nav');

    if(navBtn !== null && mainNav !== null) {
      navBtn.addEventListener('click', function() {
        navBtn.classList.toggle('menu-btn-open');
        navBtnClose.classList.toggle('menu-btn-open');
        mainNav.classList.toggle('main-nav-open');
      });
      navBtnClose.addEventListener('click', function() {
        navBtn.classList.toggle('menu-btn-open');
        navBtnClose.classList.toggle('menu-btn-open');
        mainNav.classList.toggle('main-nav-open');
      });
    }
  }
};

// Custom scripts go here
// In general, it's best to use this scripts file sparingly.
// See https://bluecoda.atlassian.net/wiki/display/BPI/Drupal+8+Best+Practices#Drupal8BestPractices-CustomTheme
// for details

