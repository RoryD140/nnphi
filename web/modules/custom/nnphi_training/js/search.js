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
        var hitTemplate = document.querySelector('#training-hit').innerHTML;

        search.addWidget(
          instantsearch.widgets.infiniteHits({
            container: '#hits',
            showMoreLabel: Drupal.t('Load More'),
            templates: {
              empty: Drupal.t('No results'),
              item: hitTemplate
            },
            transformData: {
              item: function(item) {
                return Drupal.behaviors.nnphiTrainingSearch.prepareHit(item);
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
              limit: 3,
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
              limit: 3,
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
              limit: 3,
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
              limit: 3,
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
              limit: 3,
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
              limit: 3,
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
              limit: 3,
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

        // Attach Drupal.behaviors to the rendered content.
        search.on('render', function() {
          var $new_content = $('#hits').contents();
          Drupal.attachBehaviors($new_content.get(0), settings);
        })

        search.start();
      })

    },

    /**
     * Prepare the hit object for use in the template.
     * @param item
     * @returns {*}
     */
    prepareHit: function(item) {
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
      item.headline = this.getTrainingString(headline);
      item.date = moment.unix(item.created).format('MM-DD-YY');
      if (item.url.indexOf('/') === 0){
        item.url = item.url.substring(1);
      }
      item.url = Drupal.url(item.url);
      return item;
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

  Drupal.behaviors.nnphiTrainingSearchPreview = {
    attach: function(context, settings) {
      $('a.training-preview', context).once('training-preview').each(function() {
        var $this = $(this);
        $this.qtip({
          show: 'click',
          hide: 'unfocus',
          content: {
            text: Drupal.t('Loading...'),
            ajax: {
              url: Drupal.url('node/' + $this.data('nid') + '/preview'),
              type: 'GET',
              data: {},
              success: function(data, status) {
                this.set('content.text', data.content)
              }
            }
          }
        }).bind('click', function(event){ event.preventDefault(); return false; });
      });
    }
  }
  
})(jQuery, Drupal);
