(function ($, Drupal) {
  Drupal.behaviors.nnphiBookmarkManage = {
    attach: function (context, settings) {
      $('table.user-bookmarks-table', context).once('tablesort').each(function() {
        new Tablesort(this, {
          descending: true
        });
      });

      var $behavior = this;
      $('input[data-drupal-selector="edit-default-bookmarks"]', context).once('default-folders').each(function() {
        $(this).val(JSON.stringify($behavior.selectedBookmarks));
      });

    },
    selectedBookmarks: []
  };

  /**
   * Add new command for refreshing the page.
   */
  Drupal.AjaxCommands.prototype.refreshPage = function(ajax, response, status){
    location.reload();
  };
})(jQuery, Drupal);