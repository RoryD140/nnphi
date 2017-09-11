<?php

namespace Drupal\search_api_algolia\Plugin\search_api\processor;

use Drupal\Core\Annotation\Translation;
use Drupal\search_api\Annotation\SearchApiProcessor;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api_algolia\Plugin\search_api\processor\Property\FacetAttributeProperty;

/**
 * Class FacetAttribute
 *
 * @SearchApiProcessor(
 *   id = "facet_attribute",
 *   label = @Translation("Algolia facet attribute"),
 *   description = @Translation("Adds a facet attribute to the Algolia index"),
 *   stages = {
 *    "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class FacetAttribute extends SearchableAttribute {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Algolia facet attribute'),
        'description' => $this->t('Add a field from the index to the list of facet attributes'),
        'type' => 'algolia_option',
        'processor_id' => $this->getPluginId(),
      ];

      $properties['facet_attribute'] = new FacetAttributeProperty($definition);
    }

    return $properties;
  }
}
