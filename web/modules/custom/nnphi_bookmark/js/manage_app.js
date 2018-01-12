(function (exports) {
  'use strict';

  exports.app = new Vue({
    el: '#manage-bookmarks',
    data: {
      checkedBookmarks: [],
    },
    computed: {
      addLinkText: function() {
        Drupal.behaviors.nnphiBookmarkManage.selectedFolders = this.checkedBookmarks;
        if (this.checkedBookmarks.length > 0) {
          return Drupal.t('New Folder With Selected');
        }
        else {
          return Drupal.t('New Folder');
        }
      }
    }
  });

})(window);
