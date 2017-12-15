<?php

namespace Drupal\nnphi_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\nnphi_user\Plugin\Block\UserProfile;
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
    if ($user && $this->currentUser()->id() !== $user->id()) {
      return $user->getDisplayName();
    }
    if ($user && !UserProfile::accountIsComplete($user)) {
      return $this->t('Complete Your Profile');
    }
    return $this->t('Edit Your Profile');
  }
}
