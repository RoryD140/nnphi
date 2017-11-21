(function ($, Drupal, drupalSettings) {
  $(document).ready(function () {
    var data = drupalSettings.nnphi_training_track.data;
    if (drupalSettings.hasOwnProperty('user') && drupalSettings.user.uid > 0) {
      data.uid = drupalSettings.user.uid;
    }
    else {
      return;
    }
    $.ajax({
      type: 'POST',
      cache: false,
      url: drupalSettings.nnphi_training_track.url,
      data: data
    });
  });
})(jQuery, Drupal, drupalSettings);
