<?php

namespace Drupal\nnphi_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Drupal\Component\Utility\Xss;

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
        return ['#markup' => $this->t('Complete Your Profile'), '#allowed_tags' => Xss::getHtmlTagList()];
    }
}
