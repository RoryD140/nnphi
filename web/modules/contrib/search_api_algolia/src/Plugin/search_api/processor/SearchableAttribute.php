<?php

namespace Drupal\search_api_algolia\Plugin\search_api\processor;

use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api_algolia\Plugin\search_api\processor\Property\SearchableAttributeProperty;

/**
 * Class SearchableAttribute
 *
 * @SearchApiProcessor(
 *   id = "searchable_attribute",
 *   label = @Translation("Algolia searchable attribute"),
 *   description = @Translation("Adds a field from the index to the list of searchable attributes"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class SearchableAttribute extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Algolia searchable attribute'),
        'description' => $this->t('Add a field from the index to the list of searchable attributes'),
        'type' => 'algolia_option',
        'processor_id' => $this->getPluginId(),
      ];

      $properties['searchable_attribute'] = new SearchableAttributeProperty($definition);
    }

    return $properties;
  }

  public function addFieldValues(ItemInterface $item) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function requiresReindexing(array $old_settings = NULL, array $new_settings = NULL) {
    // Index will be rebuilt in Algolia, we don't need to send all the content again.
    return FALSE;
  }
}