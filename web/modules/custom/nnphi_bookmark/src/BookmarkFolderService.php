<?php

namespace Drupal\nnphi_bookmark;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserInterface;

class BookmarkFolderService {

  private $currentUser;

  private $entityTypeManager;

  private $folderStorage;

  public function __construct(AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @param \Drupal\user\UserInterface $account
   *
   * @return array|\Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface[]
   */
  public function getFoldersForUser(UserInterface $account) {
    $query = $this->folderStorage()->getQuery();
    $fids = $query
      ->condition('uid', $account->id())
      ->execute();
    if (empty($fids)) {
      return [];
    }
    return $this->folderStorage()->loadMultiple($fids);
  }

  /**
   * Remove all bookmark folders created by a user.
   *
   * @param \Drupal\user\UserInterface $account
   */
  public function userFolderRemoval(UserInterface $account) {
    $folders = $this->getFoldersForUser($account);
    if (!empty($folders)) {
      $this->folderStorage()->delete($folders);
    }
  }

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  private function folderStorage() {
    if (empty($this->folderStorage)) {
      $this->folderStorage = $this->entityTypeManager->getStorage('bookmark_folder');
    }
    return $this->folderStorage;
  }
}
