// Set algolia noconflict.
var autocomplete = jQuery.fn.autocomplete.noConflict();
jQuery.fn.agAutocomplete = autocomplete;

(function ($, Drupal) {
  Drupal.behaviors.nnphiTrainingSearchBlock = {
    attach: function(context, settings) {
      $('.training-search-form', context).once('autocomplete').each(function() {
        var client = algoliasearch(settings.trainingSearch.app_id, settings.trainingSearch.api_key)
        var index = client.initIndex(settings.trainingSearch.index);
        var form = $(this);
        $('.training-search-input', this).agAutocomplete({ hint: false }, [
          {
            source: $.fn.agAutocomplete.sources.hits(index, { hitsPerPage: 5 }),
            displayKey: 'title',
            templates: {
              suggestion: function(suggestion) {
                return suggestion._highlightResult.title.value;
              },
              footer: '#search-autocomplete-footer'
            }
          }
        ]).on('autocomplete:selected', function(event, suggestion, dataset) {
          // Redirect to the node.
          var url = suggestion.url;
          if (url.indexOf('/') === 0){
            url = url.substring(1);
          }
          url = Drupal.url(url);
          window.location.pathname = Drupal.url(url);
        }).on('autocomplete:updated', function(event, suggestion, dataset) {
          var $this = $(this);
          var $path = $this.data('path');
          var query = $(this).val();
          $('a.advanced-search', form).attr('href', $path + '?q=' + Drupal.encodePath(query));
        });
      });
    }
  }
})(jQuery, Drupal);
