(function ($, exports) {
  'use strict';

  exports.bookmarks = new Vue({
    el: '#manage-bookmarks',
    data: {
      checkedBookmarks: [],
    },
    computed: {
      addLinkText: function() {
        Drupal.behaviors.nnphiBookmarkManage.selectedBookmarks = this.checkedBookmarks;
        if (this.checkedBookmarks.length > 0) {
          return Drupal.t('New Folder With Selected');
        }
        else {
          return Drupal.t('New Folder');
        }
      }
    }
  });

  exports.folders = new Vue({
    el: '#manage-folders',
    data: {
      checkedFolders: [],
    },
    methods: {
      launchDialog: function () {
        $('form#manage-bookmark-folders .form-submit').trigger('mousedown');
      }
    }
  });

})(jQuery, window);
