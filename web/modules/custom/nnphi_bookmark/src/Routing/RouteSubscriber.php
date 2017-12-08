<?php

namespace Drupal\nnphi_bookmark\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.bookmark_folder.collection')) {
      $route->addDefaults([
        'user' => \Drupal::currentUser()->id(),
      ]);
    }
  }

}