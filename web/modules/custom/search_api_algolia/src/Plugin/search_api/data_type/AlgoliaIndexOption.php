<?php

namespace Drupal\search_api_algolia\Plugin\search_api\data_type;

use Drupal\search_api\DataType\DataTypePluginBase;

/**
 * Provides an attribute data type.
 *
 * @SearchApiDataType(
 *   id = "algolia_option",
 *   label = @Translation("Algolia index option"),
 *   description = @Translation("Describes an indexing option within an Algolia index."),
 *   default = "false"
 * )
 */
class AlgoliaIndexOption extends DataTypePluginBase {

}

