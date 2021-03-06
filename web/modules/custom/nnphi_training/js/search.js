// For changes to this file to take effect, you need to run "grunt js" from the nnphi theme.
// For IE 11 and under, template literals (the refinementCheckbox below) don't work,
// So this needs to run through Babel for cross-browser compatibility.
// The library uses the search.min.js file, which is created when you run "grunt js"

(function ($, Drupal) {
  'use strict'
  // Initialize a seed to shuffle results consistently for the
  // the time the user is on the page.
  var trainingShuffleSeed = Math.floor(Math.random() * Math.floor(100));
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
          searchParameters: {
            getRankingInfo: true
          },
          urlSync: true
        });

        // initialize SearchBox
        search.addWidget(
          instantsearch.widgets.searchBox({
            container: '#search-box',
            placeholder: Drupal.t('Search for training')
          })
        );

        // initialize hits widget
        var hitTemplate = document.querySelector('#training-hit').innerHTML;

        search.addWidget(
          instantsearch.widgets.infiniteHits({
            container: '#hits',
            showMoreLabel: Drupal.t('Load More'),
            templates: {
              empty: Drupal.t('Nothing on that topic yet, but you can ask the <a href="@librarian">Navigator’s Learning Librarian</a> for recommendations or <a href="@training">nominate a training</a> to be reviewed.', {'@librarian': Drupal.url('librarian'), '@training': Drupal.url('nominate-training')}),
              item: hitTemplate
            },
            transformItems: function (items) {
              if (items.length) {
                // Check the ranking data to see if search parameters have been entered.
                var item = items[0];
                // If there are no filters or word matches, shuffle the results
                // using the seed generated on page load.
                if (item._rankingInfo.filters === 0 && item._rankingInfo.words === 0) {
                  return Drupal.behaviors.nnphiTrainingSearch.shuffle(items, trainingShuffleSeed);
                }
              }
              return items;
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
                header: Drupal.t('CE Credit'),
                item: refinementCheckbox
              }
            })
          );
        }
        catch (err) {}

        try {
          search.addWidget(
            instantsearch.widgets.numericRefinementList({
              container: '#length',
              attributeName: 'length',
              options: [
                {end: 1, name: Drupal.t('<=1 hour')},
                {end: 2, name: Drupal.t('<=2 hours')},
                {start: 2, name: Drupal.t('+2 hours')}
              ],
              templates: {
                header: Drupal.t('Length')
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
                header: Drupal.t('Format'),
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
                header: Drupal.t('Current Topic Areas'),
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
                header: Drupal.t('Level'),
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
                header: Drupal.t('Job Task Terms'),
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
                header: Drupal.t('Avg User Experience')
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
    },

    /**
     * Predictably shuffle an array.
     *
     * From: https://github.com/yixizhang/seed-shuffle/
     *
     * @param array
     * @param seed
     * @returns {Array}
     */
    shuffle: function shuffle(array, seed) {
      let currentIndex = array.length, temporaryValue, randomIndex;
      seed = seed || 1;
      let random = function() {
        var x = Math.sin(seed++) * 10000;
        return x - Math.floor(x);
      };
      // While there remain elements to shuffle...
      while (0 !== currentIndex) {
        // Pick a remaining element...
        randomIndex = Math.floor(random() * currentIndex);
        currentIndex -= 1;
        // And swap it with the current element.
        temporaryValue = array[currentIndex];
        array[currentIndex] = array[randomIndex];
        array[randomIndex] = temporaryValue;
      }
      return array;
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

  // Mobile search toggles
  $('.training-search-mobile-toggle').once('search-toggle').each(function() {
    $(this).click(function(){
      $(this).toggleClass('active');
      $('.training-filters').toggleClass('active');
    });
  });
  
})(jQuery, Drupal);
