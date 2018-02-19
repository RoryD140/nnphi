<?php

namespace Drupal\nnphi_bookmark;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\flag\FlaggingInterface;
use Drupal\nnphi_bookmark\Entity\BookmarkFolder;
use Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface;
use Drupal\user\UserInterface;

class BookmarkFolderService {

  use StringTranslationTrait;

  private $currentUser;

  private $entityTypeManager;

  private $folderStorage;

  private $flaggingStorage;

  private $nodeViewer;

  private $renderer;

  private $dateFormatter;

  private $nodeStorage;

  private $nodeTypeStorage;

  const FLAGGING_FOLDER_FIELD = 'field_bookmark_folder';

  public function __construct(AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager,
                              Renderer $renderer, DateFormatterInterface $dateFormatter) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * @param AccountInterface $account
   *
   * @return array|\Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface[]
   */
  public function getFoldersForUser(AccountInterface $account) {
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
   * Remove a bookmark from a bookmark folder.
   *
   * @param \Drupal\flag\FlaggingInterface $flagging
   * @param $folder_id
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function removeBookmarkFromFolder(FlaggingInterface $flagging, BookmarkFolderInterface $folder) {
    $flagging->get(BookmarkFolderService::FLAGGING_FOLDER_FIELD)->filter(function($value) use ($folder) {
      return (int)$value->getString() !== (int)$folder->id();
    });
    $flagging->save();
    // Update the folder to clear any related cache data.
    $folder->save();
  }

  /**
   * Move a bookmark from one folder to another.
   *
   * @param \Drupal\flag\FlaggingInterface $flagging
   * @param \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $from
   * @param \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $to
   */
  public function moveBookmarkToFolder(FlaggingInterface $flagging, BookmarkFolderInterface $from, BookmarkFolderInterface $to) {
    $this->removeBookmarkFromFolder($flagging, $from);
    $this->addBookmarkToFolders($flagging, [$to->id()]);
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
   * Return a row for a table listing bookmarked nodes.
   *
   * @param \Drupal\flag\FlaggingInterface $flagging
   * @param array $ops
   *
   * @return array
   */
  public function formatBookmarkTableRow(FlaggingInterface $flagging, array $ops) {
    $rating = '';
    $raw_rating = 0;
    $node = $this->nodeStorage()->load($flagging->flagged_entity->target_id);
    if ($node->hasField('field_training_review_overall') && $node->get('field_training_review_overall')->count()) {
      $field = $node->get('field_training_review_overall');
      $raw_rating = $field->getString();
      $rating = $this->nodeViewer()->viewField($field, 'mini');
      $rating = $this->renderer->render($rating);
    }
    $date = $flagging->get('created')->getString();
    $checkbox = [
      '#type' => 'checkbox',
      '#return_value' => $flagging->id(),
      '#attributes' => [
        'v-model' => 'checkedBookmarks',
      ],
    ];
    $title = $node->label();
    $link = $node->toLink($title);

    $level = '';
    if ($node->hasField('field_training_level') && !empty($levels = $node->get('field_training_level')->referencedEntities())) {
      $level = $levels[0]->label();
    }

    $proficiency = '';
    if ($node->hasField('field_training_proficiency') && !empty($proficiencies = $node->get('field_training_proficiency')->referencedEntities())) {
      $proficiency = $proficiencies[0]->label();
    }

    $ceu = '';
    if ($node->hasField('field_training_ceus_offered') && !empty($ceus = $node->get('field_training_ceus_offered')->referencedEntities())) {
      $ceu = $ceus[0]->label();
    }

    $cost_field = '';
    if($node->hasField('field_training_cost') && !$node->get('field_training_cost')->isEmpty()) {
      if($node->get('field_training_cost')->getValue()[0]['value'] === '0.00') {
        $cost_field = $this->t('Free');
      }
      else {
        $cost_field = '$' . $node->get('field_training_cost')->getString();
      }
    }

    $name_markup = [
      '#type' => 'inline_template',
      '#template' => '<div class="meta">{{ level }} {{ proficiency }} {{ cost }} {{ ceu }}</div> {{ title }}',
      '#context' => [
        'level' => $level,
        'proficiency' => $proficiency,
        'ceu' => $ceu,
        'cost' => $cost_field,
        'title' => $link,
      ],
    ];

    return [
      'checkbox' => ['data' => $this->renderer->render($checkbox)],
      'name' => ['data-sort' => $title, 'data' => $name_markup],
      'type' => ['data' => $this->getNodeTypeLabel($node->getType())],
      'created' => ['data-sort' => $date, 'data' => $this->dateFormatter->format($date, 'custom', 'n/j/Y g:i A'), 'class' => 'created'],
      'rating' => ['data-sort' => $raw_rating, 'data' => $rating, 'class' => 'ratings'],
      'options' => ['data' => $ops],
    ];
  }

  private function getNodeTypeLabel($typeId) {
    $labels = &drupal_static(__FUNCTION__, []);
    if (!isset($labels[$typeId])) {
      $type = $this->nodeTypeStorage()->load($typeId);
      $labels[$typeId] = $type->label();
    }
    return $labels[$typeId];
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

  private function nodeViewer() {
    if (empty($this->nodeViewer)) {
      $this->nodeViewer = $this->entityTypeManager->getViewBuilder('node');
    }
    return $this->nodeViewer;
  }

  private function nodeStorage() {
    if (empty($this->nodeStorage)) {
      $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    }
    return $this->nodeStorage;
  }

  private function nodeTypeStorage() {
    if (empty($this->nodeTypeStorage)) {
      $this->nodeTypeStorage = $this->entityTypeManager->getStorage('node_type');
    }
    return $this->nodeTypeStorage;
  }
}
