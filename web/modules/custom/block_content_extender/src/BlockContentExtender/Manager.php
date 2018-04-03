<?php

namespace Drupal\block_content_extender\BlockContentExtender;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;

class Manager extends DefaultPluginManager {
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/BlockContentExtender', $namespaces, $module_handler, 'Drupal\Core\Block\BlockPluginInterface', 'Drupal\block_content_extender\Annotation\BlockContentExtender');

    $this->alterInfo('block_content_extender');
    $this->setCacheBackend($cache_backend, 'block_content_extenders');
  }
}
