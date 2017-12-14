<?php

namespace Drupal\nnphi_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;

/**
 * Controller routines for user routes.
 */
class CustomUserController extends ControllerBase {
    /**
     * Route title callback.
     *
     * @param \Drupal\user\UserInterface $user
     *   The user account.
     *
     * @return string|array
     *   Override the user account name.
     *
     */
    public function userTitle(UserInterface $user = NULL) {
        return $this->t('Edit Your Profile');
    }
}
