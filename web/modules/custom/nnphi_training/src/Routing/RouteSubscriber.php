<?php

namespace Drupal\nnphi_training\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('comment.reply')) {
      $route->setRequirement('_custom_access', '\Drupal\nnphi_training\Controller\CommentController::replyFormAccess');
    }
  }

}
