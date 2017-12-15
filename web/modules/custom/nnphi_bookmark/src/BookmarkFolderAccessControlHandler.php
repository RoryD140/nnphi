<?php

namespace Drupal\nnphi_bookmark;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Bookmark folder entity.
 *
 * @see \Drupal\nnphi_bookmark\Entity\BookmarkFolder.
 */
class BookmarkFolderAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $entity */
    return AccessResult::allowedIf($account->id() === $entity->getOwnerId())
      ->orIf(AccessResult::allowedIfHasPermission($account, 'administer bookmark folder entities'));
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add bookmark folder entities');
  }

}
