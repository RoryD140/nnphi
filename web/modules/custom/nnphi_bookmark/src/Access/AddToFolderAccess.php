<?php

namespace Drupal\nnphi_bookmark\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountProxyInterface;

class AddToFolderAccess implements AccessInterface {
  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(AccountProxyInterface $account, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentUser = $account;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function access($entityType = NULL, $entityId = NULL) {
    $permission = AccessResult::allowedIfHasPermission($this->currentUser, 'flag bookmark');
    if ($entityType && $entityId) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $entity = $this->entityTypeManager->getStorage($entityType)->load($entityId);
      if ($entityType === 'node') {
        return $permission->andIf($entity->access('view', $this->currentUser)
          ->andIf(AccessResult::allowedIf($entity->getType() === 'training')));
      }
      else if ($entityType === 'flagging') {
        return $permission
          ->andIf(
            AccessResult::allowedIf($entity->getOwnerId() === $this->currentUser->id())
            ->orIf(AccessResult::allowedIfHasPermission($this->currentUser, 'administer users'))
          )
          ->andIf(AccessResult::allowedIf($entity->getFlagId() === 'bookmark'));
      }
      else {
        return AccessResult::forbidden('Invalid entity type');
      }
    }
    else {
      return $permission;
    }
  }
}