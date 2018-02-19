<?php

namespace Drupal\nnphi_bookmark;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Url;
use Drupal\flag\FlaggingInterface;
use Drupal\nnphi_bookmark\Entity\BookmarkFolder;
use Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookmarkFolderViewBuilder extends EntityViewBuilder {

  protected $entityTypeManager;

  protected $folderService;

  public function __construct(\Drupal\Core\Entity\EntityTypeInterface $entity_type, \Drupal\Core\Entity\EntityManagerInterface $entity_manager, \Drupal\Core\Language\LanguageManagerInterface $language_manager, \Drupal\Core\Theme\Registry $theme_registry = NULL,
                              EntityTypeManagerInterface $entityTypeManager, BookmarkFolderService $folderService) {
    parent::__construct($entity_type, $entity_manager, $language_manager, $theme_registry);
    $this->entityTypeManager = $entityTypeManager;
    $this->folderService = $folderService;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('theme.registry'),
      $container->get('entity_type.manager'),
      $container->get('nnphi_bookmark.folder')
    );
  }

  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    /** @var $entities BookmarkFolderInterface[] */
    if (empty($entities)) {
      return;
    }

    parent::buildComponents($build, $entities, $displays, $view_mode);

    foreach ($entities as $id => $entity) {
      $bundle = $entity->bundle();
      $display = $displays[$bundle];

      if ($display->getComponent('nodes')) {
        $build[$id]['nodes'] = $this->buildNodeList($entity);
      }
    }
  }

  /**
   * Get content for the viewed
   * @param \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $bookmarkFolder
   *
   * @return array
   */
  protected function buildNodeList(BookmarkFolderInterface $bookmarkFolder) {
    $flag_storage = $this->entityTypeManager->getStorage('flagging');
    $flaggings = $flag_storage
      ->getQuery()
      ->condition('flag_id', 'bookmark')
      ->condition('uid', $bookmarkFolder->getOwnerId())
      ->condition('field_bookmark_folder', $bookmarkFolder->id())
      ->pager()
      ->sort('created', 'DESC')
      ->execute();
    if (empty($flaggings)) {
      return $this->getEmptyContent();
    }
    $flaggings = $flag_storage->loadMultiple($flaggings);
    return [
      'nodes' => $this->buildTable($flaggings, $bookmarkFolder),
      'pager' => ['#type' => 'pager'],
    ];
  }

  /**
   * @param \Drupal\flag\FlaggingInterface[]
   */
  private function buildTable(array $flaggings, BookmarkFolderInterface $bookmarkFolder) {
    $user_id = $bookmarkFolder->getOwnerId();
    $header = [
      'name' => ['data' => $this->t('Name'), 'class' => ['sort-column', 'name-cell']],
      'type' => ['data' => $this->t('Type'), 'data-sort-method' => 'none', 'class' => 'type-cell'],
      'created' => ['data-sort-default' => 1, 'data' => $this->t('Date Added'), 'class' => ['sort-column', 'created-cell']],
      'rating' => ['data' => $this->t('Rating'), 'class' => ['sort-column', 'rating-cell']],
      'options' => ['data' => '', 'data-sort-method' => 'none', 'class' => 'options-cell']
    ];
    $build = [];
    $rows = [];
    foreach ($flaggings as $flagging) {
      $row = $this->folderService->formatBookmarkTableRow($flagging, $this->getRowOptions($flagging, $bookmarkFolder));
      unset($row['checkbox']);
      $rows[] = $row;
      CacheableMetadata::createFromObject($flagging)
        ->applyTo($build);
    }
    $build['breadcrumbs'] = [
      '#title' => $this->t('Bookmarked Trainings'),
      '#type' => 'link',
      '#url' => Url::fromRoute('nnphi_bookmark.user_list', ['user' => $user_id]),
      '#prefix' => '<div class="breadcrumbs">',
      '#suffix' => '<span class="folder-name">' . $bookmarkFolder->label() . '</span></div>'
    ];
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => [
        'class' => [
          'user-bookmarks-table',
          'orphan-flags',
          'user-bookmarks-folders-table',
          // Bootstrap table classes.
          'table',
          'table-responsive',
          'table-hover'
        ],
      ],
      '#prefix' => '<div id="manage-bookmarks" class="table-wrapper">',
      '#suffix' => '</div>',
      '#attached' => [
        'library' => [
          'nnphi_bookmark/manage_bookmarks',
        ],
      ],
    ];

    return $build;
  }

  protected function getEmptyContent() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('You have not saved any trainings in this folder.'),
    ];
  }

  protected function getRowOptions(FlaggingInterface $flagging, BookmarkFolderInterface $bookmarkFolder) {
    $node = $this->entityTypeManager->getStorage('node')->load($flagging->get('entity_id')->getString());

    $build['options_toggle'] = [
      '#type' => 'button',
      '#value' => '...',
      '#url' => '/',
      '#attributes' => [
        'class' => ['dropdown','dropdown-toggle'],
        'id' => 'dropdownMenuButton',
        'data-toggle' => 'dropdown',
        'aria-label' => $this->t('Other Options'),
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
        'role' => 'button'
      ],
      '#prefix' => '<div class="dropdown">'
    ];

    $build['open'] = [
      '#prefix' => '<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">',
      '#title' => $this->t('Open'),
      '#type' => 'link',
      '#url' => $node->toUrl(),
      '#attributes' => ['class' => ['dropdown-item']]
    ];

    $build['remove'] = [
      '#title' => $this->t('Remove from Folder'),
      '#type' => 'link',
      '#url' => Url::fromRoute('nnphi_bookmark.remove_from_folder',
        ['user' => $bookmarkFolder->getOwnerId(), 'bookmark_folder' => $bookmarkFolder->id(), 'flagging' => $flagging->id()],
        ['attributes' => ['class' => ['use-ajax']]]),
      '#attributes' => ['class' => ['dropdown-item']]
    ];

    $build['move'] = [
      '#title' => $this->t('Move to Folder'),
      '#type' => 'link',
      '#url' => Url::fromRoute('nnphi_bookmark.move_to_folder',
        ['user' => $bookmarkFolder->getOwnerId(), 'bookmark_folder' => $bookmarkFolder->id(), 'flagging' => $flagging->id()],
        ['attributes' => ['class' => ['use-ajax']]]),
      '#attributes' => ['class' => ['dropdown-item']]
    ];

    $build['suffix'] = [
      '#type' => 'markup',
      '#markup' => '</div></div>'
    ];

    return $build;
  }

}
