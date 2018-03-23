(function ($, Drupal) {
  Drupal.behaviors.trainingPage = {
    attach: function (context, settings) {
      $('div.search-back', context).once('training-page').each(function() {
        // If the user arrived from the search, add a "back" link.
        if (window.location.search.indexOf('src=search') > -1) {
          var location = $.cookie('nnphi_search_url');
          if (location.length > 0) {
            var $this = $(this);
            $this.html('<a class="search-back-link" href="' + location +'">' + Drupal.t('Back to Search Results') + '</a>');
            $this.show();
          }
        }
      });
    }
  };
})(jQuery, Drupal);
