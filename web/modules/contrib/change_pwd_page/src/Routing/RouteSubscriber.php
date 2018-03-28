<?php

namespace Drupal\change_pwd_page\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // As Change Password page is separate form now so in order to do that,
    // override user.reset route with change_pwd_page.reset route to show the
    // Change Password from instead of default.
    if ($route = $collection->get('user.reset')) {
      $route->setDefault('_controller', 'Drupal\change_pwd_page\Controller\ChangePwdPageController::resetPass');
    }
  }
}
