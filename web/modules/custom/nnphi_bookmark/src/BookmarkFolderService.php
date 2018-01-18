<?php

namespace Drupal\nnphi_bookmark;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\flag\FlaggingInterface;
use Drupal\nnphi_bookmark\Entity\BookmarkFolder;
use Drupal\user\UserInterface;

class BookmarkFolderService {

  private $currentUser;

  private $entityTypeManager;

  private $folderStorage;

  private $flaggingStorage;

  const FLAGGING_FOLDER_FIELD = 'field_bookmark_folder';

  public function __construct(AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @param \Drupal\user\UserInterface $account
   *
   * @return array|\Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface[]
   */
  public function getFoldersForUser(AccountProxyInterface $account) {
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
   * Add a bookmark to bookmark folder(s).
   *
   * @param \Drupal\flag\FlaggingInterface $bookmark
   * @param array $folder_ids
   * @return \Drupal\flag\FlaggingInterface
   */
  public function addBookmarkToFolders(FlaggingInterface $bookmark, array $folder_ids) {
    $value = [];
    foreach ($folder_ids as $folder_id) {
      $value[] = ['target_id' => $folder_id];
    }
    $bookmark->set(self::FLAGGING_FOLDER_FIELD, $value);
    $bookmark->save();
    return $bookmark;
  }

  /**
   * Combine bookmark_folders into one folder.
   *
   * @param \Drupal\nnphi_bookmark\Entity\BookmarkFolder $destination
   * @param array $sources An array of bookmark_folder entities to combine.
   * @param bool $delete Delete the leftover folders.
   */
  public function combineFolders(BookmarkFolder $destination, array $sources, $delete = TRUE) {
    /** @var \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $sourceFolder */
    foreach ($sources as $sourceFolder) {
      $flagging_ids = $this->flaggingStorage()->getQuery()
        ->condition(self::FLAGGING_FOLDER_FIELD, $sourceFolder->id())
        ->execute();
      if (!empty($flagging_ids)) {
        /** @var FlaggingInterface[] $flaggings */
        $flaggings = $this->flaggingStorage()->loadMultiple($flagging_ids);
        foreach ($flaggings as $flagging) {
          $flagging->get(self::FLAGGING_FOLDER_FIELD)->appendItem(['target_id' => $destination->id()]);
          $flagging->save();
        }
      }
      if ($delete && (int)$sourceFolder->id() !== (int)$destination->id()) {
        $sourceFolder->delete();
      }
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

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  private function flaggingStorage() {
    if (empty($this->flaggingStorage)) {
      $this->flaggingStorage = $this->entityTypeManager->getStorage('flagging');
    }
    return $this->flaggingStorage;
  }
}
