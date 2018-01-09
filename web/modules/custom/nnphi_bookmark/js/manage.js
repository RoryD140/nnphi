(function ($, Drupal) {
  Drupal.behaviors.nnphiBookmarkManage = {
    attach: function (context, settings) {
      $('table.user-bookmarks-table').once('tablesort').each(function() {
        new Tablesort(this, {
          descending: true
        });
      });
    }
  };
})(jQuery, Drupal);