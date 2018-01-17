<?php

namespace Drupal\nnphi_bookmark\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\flag\FlaggingInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\nnphi_bookmark\BookmarkFolderService;
use Drupal\nnphi_bookmark\Entity\BookmarkFolder;
use Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface;
use Drupal\nnphi_bookmark\Form\ManageBookmarks;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserBookmarkFolders extends ControllerBase {

  private $folderService;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\node\NodeStorageInterface|null
   */
  private $nodeStorage;

  public function __construct(BookmarkFolderService $folderService, FlagServiceInterface $flagService,
                              RendererInterface $renderer, DateFormatterInterface $dateFormatter) {
    $this->folderService = $folderService;
    $this->flagService = $flagService;
    $this->renderer = $renderer;
    $this->dateFormatter = $dateFormatter;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nnphi_bookmark.folder'),
      $container->get('flag'),
      $container->get('renderer'),
      $container->get('date.formatter')
    );
  }

  /**
   * User bookmark page callback.
   *
   * @param \Drupal\user\UserInterface $user
   *
   * @return array
   */
  public function page(UserInterface $user) {
    $build = [];
    $build['#theme'] = 'user_bookmarks_page';
    $build['#bookmarks'] = $this->getUserBookmarks($user);
    $build['#folders'] = $this->getUserFolders($user);

    // Add user metadata to cache array.
    CacheableMetadata::createFromObject($user)
      ->applyTo($build);
    $build['#cache']['keys'] = ['user', 'user_bookmark_folder_list', $user->id()];
    $build['#attached']['library'][] = 'nnphi_bookmark/manage_bookmarks.app';

    return $build;
  }


  /**
   * Check access to the user bookmarks page.
   *
   * @param \Drupal\user\UserInterface $account
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   */
  public function checkAccess(UserInterface $user) {
    $account = \Drupal::currentUser();

    if ($account->hasPermission('administer bookmark folder entities')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    if ($user->id() == $account->id()) {
      return AccessResult::allowedIfHasPermission($account, 'add bookmark folder entities')->cachePerPermissions()->cachePerUser();
    }

    return AccessResult::neutral()->cachePerPermissions();
  }

  private function getUserFolders(UserInterface $user) {
    $build = [];
    $header = [
      'check' => ['data' => '', 'data-sort-method' => 'none', 'width' => '10%'],
      'name' => ['data' => $this->t('Name'), 'width' => '90%'],
      'opts' => ['data' => '', 'data-sort-method' => 'none'],
    ];
    $rows = [];
    $fids = $this->entityTypeManager()->getStorage('bookmark_folder')->getQuery()
      ->condition('uid', $user->id())
      ->execute();
    if (empty($fids)) {
      return $build;
    }
    /** @var \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface[] $folders */
    $folders = $this->entityTypeManager()->getStorage('bookmark_folder')->loadMultiple($fids);
    foreach ($folders as $folder) {
      CacheableMetadata::createFromObject($folder)
        ->applyTo($build);
      $checkbox = [
        '#type' => 'checkbox',
        '#value' => $folder->id(),
        '#attributes' => [
          'v-model' => 'folders',
        ],
      ];
      $rows[$folder->id()] = [
         ['data' =>  \Drupal::service('renderer')->render($checkbox)],
          ['data-sort' => $folder->label(), 'data' => $folder->toLink($folder->label()), 'class' => 'folder-title'],
          ['data' => $this->getFolderOptions($folder)],
      ];
    }

    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => [
        'class' => [
          'user-bookmarks-folders-table',
          // Bootstrap table classes.
          'table',
          'table-responsive-md',
          'table-hover'
        ],
      ]
    ];

//    $build['#cache']['keys'] = ['user', 'bookmark_folders', $user->id()];

    return $build;
  }

  protected function getFolderOptions(BookmarkFolderInterface $folder) {
    $links = [];
    $links['view'] = [
      'title' => $this->t('View'),
      'url' => $folder->toUrl(),
    ];
    $links['edit'] = [
      'title' => $this->t('Edit'),
      'url' => Url::fromRoute('entity.bookmark_folder.edit_form',
        ['user' => $folder->getOwnerId(), 'bookmark_folder' => $folder->id()],
        ['attributes' => ['class' => ['use-ajax'], 'data-dialog-type' => 'modal', 'data-dialog-options' => Json::encode(['width' => '75%'])]]
      ),
    ];
    $links['delete'] = [
      'title' => $this->t('Delete'),
      'url' => Url::fromRoute('entity.bookmark_folder.delete_form',
        ['user' => $folder->getOwnerId(), 'bookmark_folder' => $folder->id()],
        ['attributes' => ['class' => ['use-ajax'], 'data-dialog-type' => 'modal', 'data-dialog-options' => Json::encode(['width' => '75%'])]]
      ),
    ];
    return [
      '#type' => 'dropbutton',
      '#links' => $links,
    ];
  }

  /**
   * Get
   * @param \Drupal\user\UserInterface $user
   *
   * @return bool|\Drupal\flag\FlaggingInterface[]
   */
  private function getUserBookmarks(UserInterface $user) {
    $fs = $this->entityTypeManager()->getStorage('flagging');
    $ns = $this->entityTypeManager()->getStorage('node');
    $nodeViewer = $this->entityTypeManager()->getViewBuilder('node');
    $build = [];
    $header = [
      'checkbox' => ['data' => '', 'data-sort-method' => 'none'],
      'name' => $this->t('Name'),
      'created' => ['data-sort-default' => 1, 'data' => $this->t('Date Added')],
      'rating' => $this->t('Rating'),
      'delete' => ['data' => '', 'data-sort-method' => 'none'],
      'options' => ['data' => '', 'data-sort-method' => 'none'],
    ];

    $fids = $this->entityTypeManager()->getStorage('flagging')->getQuery()
      ->condition('flag_id', 'bookmark')
      ->condition('uid', $user->id())
      ->notExists('field_bookmark_folder')
      ->sort('created', 'DESC')
      ->execute();
    if (empty($fids)) {
      return FALSE;
    }

    $rows = [];

    /** @var \Drupal\flag\FlaggingInterface $flagging */
    foreach ($fs->loadMultiple($fids) as $flagging) {
      $nid = $flagging->get('entity_id')->getString();
      /** @var \Drupal\node\NodeInterface $node */
      $node = $ns->load($nid);
      $rating = '';
      $raw_rating = 0;
      if ($node->hasField('field_training_overall_rating') && $node->get('field_training_overall_rating')->count()) {
        $field = $node->get('field_training_overall_rating');
        $raw_rating = $field->getString();
        $rating = $nodeViewer->viewField($field, 'mini');
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
      $fid = $flagging->id();
      $rows[$fid] = [
        'checkbox' => ['data' => $this->renderer->render($checkbox)],
        'name' => ['data-sort' => $title, 'data' => $node->toLink($title)],
        'created' => ['data-sort' => $date, 'data' => $this->dateFormatter->format($date, 'custom', 'n/j/Y g:i A')],
        'rating' => ['data-sort' => $raw_rating, 'data' => $rating],
        'delete' => ['data' => Link::createFromRoute($this->t('Delete'),
          'nnphi_bookmark.delete_flagging', ['flagging' => $fid], ['attributes' => ['class' => ['use-ajax', 'bookmark-delete']]])],
        'options' => ['data' => $this->getFlaggingOptions($flagging)],
      ];
    }

    $build['bookmarks'] = [
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
          'table-responsive-md',
          'table-hover'
        ],
      ],
    ];

    return $build;
  }

  protected function getFlaggingOptions(FlaggingInterface $flagging) {
    $links = [];
    $node = $this->nodeStorage()->load($flagging->get('entity_id')->getString());
    $links['view'] = [
      'title' => $this->t('View'),
      'url' => $node->toUrl(),
    ];
    $links['new'] = [
      'title' => $this->t('Create Folder from Bookmark'),
      'url' => Url::fromRoute('nnphi_bookmark.create_folder',
        ['entityId' => $flagging->id(), 'entityType' => $flagging->getEntityTypeId()],
        ['attributes' => ['class' => ['use-ajax'], 'data-dialog-type' => 'modal']]
      ),
    ];
    $links['add'] = [
      'title' => $this->t('Add to Existing Folder'),
      'url' => Url::fromRoute('nnphi_bookmark.add_to_folder',
                ['flagging' => $flagging->id()],
                ['attributes' => ['class' => ['use-ajax'], 'data-dialog-type' => 'modal']]),
    ];
    return [
      '#type' => 'dropbutton',
      '#links' => $links,
    ];
  }

  public function addFolder(Request $request, $entityType = NULL, $entityId = NULL) {
    $defaultEntity = FALSE;
    if ($entityType && $entityId) {
      $defaultEntity = $this->entityTypeManager()->getStorage($entityType)
        ->load($entityId);
    }
    $folder = $this->entityTypeManager()->getStorage('bookmark_folder')
      ->create(['uid' => $this->currentUser()->id()]);
    $form = $this->entityFormBuilder()->getForm($folder, 'default', [
      'defaultEntity' => $defaultEntity,
    ]);
    if ($request->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand($this->t('Add Folder'), $form, [
        'width' => '40%',
      ]));
      return $response;
    }
    return $form;
  }

  public function addToFolder(FlaggingInterface $flagging) {
    $form = $this->formBuilder()->getForm(\Drupal\nnphi_bookmark\Form\AddToFolder::class, $flagging);
    return $form;
  }

  /**
   * @return \Drupal\node\NodeStorageInterface;
   */
  private function nodeStorage() {
    if (empty($this->nodeStorage)) {
      $this->nodeStorage = $this->entityTypeManager()->getStorage('node');
    }
    return $this->nodeStorage;
  }
}
