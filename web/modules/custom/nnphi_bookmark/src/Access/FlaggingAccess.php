<?php

namespace Drupal\nnphi_bookmark\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\flag\FlaggingInterface;

class FlaggingAccess implements AccessInterface {

  protected $currentUser;

  public function __construct(AccountProxyInterface $account) {
    $this->currentUser = $account;
  }

  public function access(RouteMatchInterface $route_match, FlaggingInterface $flagging) {
    return AccessResult::allowedIf($flagging->getOwnerId() == $this->currentUser->id())
      ->orIf(AccessResult::allowedIf($this->currentUser->hasPermission('administer users')))
      ->addCacheableDependency($flagging);
  }
}