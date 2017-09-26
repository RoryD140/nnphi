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
            placeholder: Drupal.t('Search for trainings')
          })
        );

        // initialize hits widget
        search.addWidget(
          instantsearch.widgets.infiniteHits({
            container: '#hits',
            showMoreLabel: Drupal.t('Load More'),
            templates: {
              empty: Drupal.t('No results'),
              item: function(item) {
                return Drupal.theme('trainingSearchResult', item);
              }
            },
            escapeHits: true
          })
        );

        try {
          search.addWidget(
            instantsearch.widgets.stats({
              container: '#stats',
              templates: {
                body: function(stats) {
                  return Drupal.formatPlural(stats.nbHits, '@count Result', '@count Results');
                }
              }
            })
          );
        }
        catch (err) {}

        /********Facets*********/

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#ceus',
              attributeName: 'ceu',
              operator: 'or',
              limit: 5,
              showMore: true,
              templates: {
                header: Drupal.t('CEUS')
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#course-types',
              attributeName: 'course_type',
              operator: 'or',
              limit: 5,
              showMore: true,
              templates: {
                header: Drupal.t('Course Types')
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#levels',
              attributeName: 'course_level',
              operator: 'or',
              limit: 5,
              showMore: true,
              templates: {
                header: Drupal.t('Levels')
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#locations',
              attributeName: 'field_training_state',
              operator: 'or',
              limit: 5,
              showMore: true,
              templates: {
                header: Drupal.t('Related Location')
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#competency',
              attributeName: 'competencies',
              operator: 'or',
              limit: 5,
              showMore: true,
              templates: {
                header: Drupal.t('Competency Terms')
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#occupation',
              attributeName: 'occupations',
              operator: 'or',
              limit: 5,
              showMore: true,
              templates: {
                header: Drupal.t('Occupation')
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#job-tasks',
              attributeName: 'job_tasks',
              operator: 'or',
              limit: 5,
              showMore: true,
              templates: {
                header: Drupal.t('Specific Job Task Terms')
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.clearAll({
              container: '#reset',
              templates: {
                link: Drupal.t('Reset All Filters')
              },
              autoHideContainer: false,
              clearsQuery: true,
            })
          );
        }
        catch (err) {}

        search.start();
      })

    },

    /**
     * Return the course info string based on items.
     *
     * @param items
     * @returns {string}
     */
    getTrainingString: function(items) {
      return items.join(' ');
    }

  };

  /**
   * Theme an individual Algolia search result.
   *
   * TODO: Is this output safe?
   *
   * @param item
   * @returns {string}
   */
  Drupal.theme.trainingSearchResult = function (item) {
    var headline = [];

    if (item.hasOwnProperty('course_level')) {
      headline.push(item.course_level);
    }
    if (item.hasOwnProperty('ceu')) {
      if (typeof item.ceu === 'object') {
        headline.push(item.ceu[0]);
      }
      else {
        headline.push(item.ceu);
      }
    }
    if (item.hasOwnProperty('course_type')) {
      headline.push(item.course_type);
    }
    // Determine the cost.
    if (item.hasOwnProperty('field_training_cost')) {
      if (item.field_training_cost === 0) {
        headline.push(Drupal.t('Free'));
      }
      else {
        headline.push(item.cost);
      }
    }

    var html = '<article class="training-node-teaser" role="article">';

    html += '<div class="training-node-teaser-top">';

    html += '<div class="training-node-teaser-top-left">';
    html += Drupal.behaviors.nnphiTrainingSearch.getTrainingString(headline);
    html += '</div>'; // teaser-top-left

    html += '<div class="training-node-teaser-top-right">';
    html += moment.unix(item.created).format('MM-DD-YY');
    html += '<div>' + item.training_source + '</div>';
    html += '</div>'; // teaser-top-right

    html += '</div>'; // teaser-top

    html += '<h3>' + item._highlightResult.title.value + '</h3>';
    html += '<div>' + item.field_training_description + '</div>';

    html += '<div class="training-teaser-link-wrapper">';
    html += '<a class="training-teaser-link" href="' + item.url + '">' + Drupal.t('View Training') + '</a>';
    html += '</div>';

    html += '</article>';

    return html;
  };
  
})(jQuery, Drupal);
