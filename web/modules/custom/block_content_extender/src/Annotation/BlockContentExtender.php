<?php

namespace Drupal\block_content_extender\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a BlockContentExtender annotation object.
 *
 * @Annotation
 */
class BlockContentExtender extends Plugin {
  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The block_content bundles the plugin applies to.
   *
   * @var array
   */
  public $bundles;
}
