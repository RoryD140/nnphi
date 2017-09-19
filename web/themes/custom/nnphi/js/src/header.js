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
