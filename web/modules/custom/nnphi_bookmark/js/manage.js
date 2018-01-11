(function ($, Drupal) {
  Drupal.behaviors.nnphiBookmarkManage = {
    attach: function (context, settings) {
      $('table.user-bookmarks-table').once('tablesort').each(function() {
        new Tablesort(this, {
          descending: true
        });
      });

      $('table.orphan-flags .form-checkbox').once('bookmark-add').each(function () {
        var $this = $(this);
        $this.click(function() {
          
        });
      });
    },

    selectedFlags: {}
  };
})(jQuery, Drupal);