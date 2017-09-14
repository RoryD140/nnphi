(function ($, Drupal) {
  Drupal.behaviors.nnphiTrainingSearch = {
    attach: function (context, settings) {
      $('#hits', context).once('search').each(function()  {
        const search = instantsearch({
          appId: settings.trainingSearch.app_id,
          apiKey: settings.trainingSearch.api_key,
          indexName: settings.trainingSearch.index,
          urlSync: true
        });

        // initialize SearchBox
        search.addWidget(
          instantsearch.widgets.searchBox({
            container: '#search-box',
            placeholder: Drupal.t('Enter search terms')
          })
        );

        // initialize hits widget
        search.addWidget(
          instantsearch.widgets.hits({
            container: '#hits',
            templates: {
              empty: 'No results',
              item: '<strong>Hit {{objectID}}</strong>: {{_highlightResult.body.value}}'
              // item: function(item) {
              //   return item._highlightResult.body.value
              // }
            },
            escapeHits: true,
          })
        );

        search.addWidget(
          instantsearch.widgets.rangeSlider({
            container: '#facets',
            attributeName: 'field_training_rating',
            templates: {
              header: 'Training Rating'
            },
            tooltips: {
              format: function(rawValue) {
                return  Math.round(rawValue).toLocaleString();
              }
            }
          })
        );


        search.start();
      })

    }
  };
})(jQuery, Drupal);