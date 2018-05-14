<?php

namespace Drupal\nnphi_user\Plugin\Menu;


use Drupal\user\Plugin\Menu\LoginLogoutMenuLink;

/**
 * A menu link that shows "Sign in" or "Sign out" as appropriate.
 */
class NnphiLoginLogoutMenuLink extends LoginLogoutMenuLink {


  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    if ($this->currentUser->isAuthenticated()) {
      return $this->t('Sign out');
    }
    else {
      return $this->t('Sign in');
    }
  }

}
