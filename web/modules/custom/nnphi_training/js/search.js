(function ($, Drupal) {
  'use strict'
  Drupal.behaviors.nnphiTrainingSearch = {
    attach: function (context, settings) {
      // Create a new template for the custom checkbox theming.
      var refinementCheckbox = `<label class="{{cssClasses.label}}">
                                <span class="custom-checkbox">
                                <input type="checkbox"
                                       class="{{cssClasses.checkbox}}"
                                       value="{{value}}"
                                       {{#isRefined}}checked{{/isRefined}} /><span class="checkbox-target"></span></span>
                                    {{{highlighted}}}
                                <span class="{{cssClasses.count}}">{{#helpers.formatNumber}}{{count}}{{/helpers.formatNumber}}</span>
                              </label>`;


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
              container: '#content-type',
              attributeName: 'content_type',
              operator: 'or',
              limit: 2,
              showMore: false,
              templates: {
                header: Drupal.t('Type'),
                item: refinementCheckbox
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#ceus',
              attributeName: 'ceu',
              operator: 'or',
              limit: 3,
              showMore: true,
              templates: {
                header: Drupal.t('CEUS'),
                item: refinementCheckbox
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
                header: Drupal.t('Course Types'),
                item: refinementCheckbox
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.refinementList({
              container: '#topic-areas',
              attributeName: 'topic_areas',
              operator: 'or',
              limit: 3,
              showMore: true,
              templates: {
                header: Drupal.t('Topic Areas'),
                item: refinementCheckbox
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
                header: Drupal.t('Levels'),
                item: refinementCheckbox
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
                header: Drupal.t('Competency Terms'),
                item: refinementCheckbox
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
                header: Drupal.t('Occupation'),
                item: refinementCheckbox
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
                header: Drupal.t('Specific Job Task Terms'),
                item: refinementCheckbox
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

        try {
          search.addWidget(
            instantsearch.widgets.starRating({
              container: '#rating',
              attributeName: 'overall_rating_facet_value',
              max: 5,
              labels: {
                andUp: Drupal.t('& Up')
              },
              templates: {
                header: Drupal.t('Avg User Rating')
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.rangeSlider({
              container: '#cost',
              attributeName: 'field_training_cost',
              min: 0,
              templates: {
                header: 'Price'
              },
              tooltips: {
                format: function (rawValue) {
                  if (rawValue === 0) {
                    return Drupal.t('Free');
                  }
                  else {
                    return '$' + Math.round(rawValue).toLocaleString();
                  }
                }
              }
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
      });

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
      item.url = Drupal.url(item.url) + '?src=search';

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
      $('a.training-preview-link', context).once('training-preview').each(function() {
        var $this = $(this);
        $this.qtip({
          show: {
            event: 'click',
            modal: {
              on: true
            }
          },
          hide: {
            event: false,
            effect: 'fade'
          },
          content: {
            text: Drupal.t('Loading...'),
            ajax: {
              url: Drupal.url('node/' + $this.data('nid') + '/preview'),
              type: 'GET',
              data: {},
              success: function(data, status) {
                var $content = $(this.elements.content);
                this.set('content.text', data.content);

                // Add a class so that we can remove loading padding
                $content.addClass('qtip-content-loaded');

                Drupal.attachBehaviors($content.get(0), settings);

                // Assign the hide event listener to the close button
                $content.find('.training-node-preview-close').click(function() {
                    $this.qtip('hide');
                  }
                );
              }
            }
          },
          position: {
            my: 'center', // Center within window
            at: 'center',
            target: $(window),
            adjust: {
              method: 'none',
              resize: true
            }
          },
          style: {
            classes: 'nnphi-qtip-wrapper',
            def: false // Remove the default styling
          }
        }).bind('click', function(event){ event.preventDefault(); return false; });
      });
      $('a.training-teaser-link, a.training-node-preview-btn', context).once('search-history').click(function(e) {
        // Set a cookie so training pages can add a back link.
        var url = $(this).attr('href');
        e.preventDefault();
        $.cookie('nnphi_search_url', window.location);
        document.location = url;
      });
    }
  }
  
})(jQuery, Drupal);
