<?php

namespace Drupal\nnphi_user\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

    /**
     * {@inheritdoc}
     */
    public function alterRoutes(RouteCollection $collection) {
        // Retrieve the user edit form route.
        if ($route = $collection->get('entity.user.edit_form')) {
            $route->setDefault('_title_callback','Drupal\nnphi_user\Controller\CustomUserController::userTitle');
        }

        // Adjust page titles on login/register pages
      if ($route = $collection->get('user.login')) {
        $route->setDefault('_title', 'Sign In');
      }
      if ($route = $collection->get('user.register')) {
        $route->setDefault('_title', 'Sign Up');
      }
    }

}