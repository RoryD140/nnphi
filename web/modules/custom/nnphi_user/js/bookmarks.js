(function ($, Drupal) {
  Drupal.behaviors.trainingBookmarks = {
    attach: function (context, settings) {
      if ($(context).hasClass('action-flag')) {
        var $bookmark = $(context).closest('.training-bookmark');
        $bookmark.remove();
        var $bookmarks_wrapper = $('.training-bookmarks');
        if ($bookmarks_wrapper.html().length < 1) {
          $bookmarks_wrapper.html(Drupal.t('You have not bookmarked any trainings.'));
        }
      }
    }
  };
})(jQuery, Drupal);
