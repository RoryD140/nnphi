<?php

namespace Drupal\nnphi_training\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;

class InstantSearch extends ControllerBase {
  public function page() {
    return [
      '#theme' => 'nnphi_training_search_page',
      '#cache' => [
        'max-age' => Cache::PERMANENT,
      ],
    ];
  }
}
