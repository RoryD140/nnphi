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

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeTypeStorage;

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
    return $this->formBuilder()->getForm(\Drupal\nnphi_bookmark\Form\ManageBookmarkFolders::class, $user);
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
      'checkbox' => ['data' => '', 'data-sort-method' => 'none', 'class' => 'checkbox-cell'],
      'name' => ['data' => $this->t('Name'), 'class' => ['sort-column', 'name-cell']],
      'type' => ['data' => $this->t('Type'), 'data-sort-method' => 'none', 'class' => 'type-cell'],
      'created' => ['data-sort-default' => 1, 'data' => $this->t('Date Added'), 'class' => ['sort-column', 'created-cell']],
      'rating' => ['data' => $this->t('Rating'), 'class' => ['sort-column', 'rating-cell']],
      'options' => ['data' => '', 'data-sort-method' => 'none', 'class' => 'options-cell']
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
      $rows[$flagging->id()] = $this->folderService->formatBookmarkTableRow($flagging, $this->getFlaggingOptions($flagging));
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
          'table-responsive',
          'table-hover'
        ],
      ],
      '#prefix' => '<div class="table-wrapper">',
      '#suffix' => '</div>'
    ];

    return $build;
  }

  protected function getFlaggingOptions(FlaggingInterface $flagging) {

    $node = $this->nodeStorage()->load($flagging->get('entity_id')->getString());

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

    $build['new'] = [
      '#title' => $this->t('Create Folder from Bookmark'),
      '#type' => 'link',
      '#url' => Url::fromRoute('nnphi_bookmark.create_folder',
        ['entityId' => $flagging->id(), 'entityType' => $flagging->getEntityTypeId()],
        ['attributes' => ['class' => ['use-ajax', 'dropdown-item'], 'data-dialog-type' => 'modal', 'data-dialog-options' => Json::encode(['width' => '75%', 'top' => '10rem'])]]
      )
    ];

    $build['add'] = [
      '#title' => $this->t('Add to Existing Folder'),
      '#type' => 'link',
      '#url' => Url::fromRoute('nnphi_bookmark.add_to_folder',
        ['flagging' => $flagging->id()],
        ['attributes' => ['class' => ['use-ajax', 'dropdown-item'], 'data-dialog-type' => 'modal', 'data-dialog-options' => Json::encode(['width' => '75%', 'top' => '10rem'])]]),
    ];

    $build['delete'] = [
      '#title' => $this->t('Delete'),
      '#type' => 'link',
      '#url' => Url::fromRoute('nnphi_bookmark.delete_flagging',
        ['flagging' => $flagging->id()],
        ['attributes' => ['class' => ['use-ajax', 'dropdown-item'], 'data-dialog-type' => 'modal', 'data-dialog-options' => Json::encode(['width' => '75%', 'top' => '10rem'])]]),
    ];

    return $build;
  }

  /**
   * Add folder page callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param null $entityType
   * @param null $entityId
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
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
        'width' => '75%',
      ]));
      return $response;
    }
    return $form;
  }

  /**
   * Add to folder page callback.
   *
   * @param \Drupal\flag\FlaggingInterface $flagging
   *
   * @return array
   */
  public function addToFolder(FlaggingInterface $flagging) {
    $form = $this->formBuilder()->getForm(\Drupal\nnphi_bookmark\Form\AddToFolder::class, $flagging);
    return $form;
  }

  /**
   * Custom access callback for folder/flagging combination.
   *
   * @param \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $bookmark_folder
   * @param \Drupal\flag\FlaggingInterface $flagging
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden|\Drupal\Core\Access\AccessResultNeutral|static
   */
  public function removeFromFolderAccess(BookmarkFolderInterface $bookmark_folder, FlaggingInterface $flagging) {
    return AccessResult::allowedIf((int)$bookmark_folder->getOwnerId() === (int)$this->currentUser()->id())
      ->andIf(AccessResult::allowedIf((int)$flagging->getOwnerId() === (int)$this->currentUser()->id()))
      ->orIf(AccessResult::allowedIfHasPermission($this->currentUser(), 'administer bookmark folder entities'));
  }

  /**
   * Remove from folder page callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $bookmark_folder
   * @param \Drupal\flag\FlaggingInterface $flagging
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   */
  public function removeFromFolder(Request $request, BookmarkFolderInterface $bookmark_folder, FlaggingInterface $flagging) {
    $form = $this->formBuilder()->getForm(\Drupal\nnphi_bookmark\Form\RemoveFromFolder::class, $flagging, $bookmark_folder);
    if ($request->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $title = $this->t('Remove bookmark from folder');
      $response->addCommand(new OpenModalDialogCommand($title, $form, [
        'width' => '75%',
      ]));
      return $response;
    }
    return $form;
  }

  /**
   * Move to folder page callback.
   * 
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\flag\FlaggingInterface $flagging
   * @param \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $bookmark_folder
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   */
  public function moveToFolder(Request $request, FlaggingInterface $flagging, BookmarkFolderInterface $bookmark_folder) {
    $form = $this->formBuilder()->getForm(\Drupal\nnphi_bookmark\Form\MoveToFolder::class, $flagging, $bookmark_folder);
    if ($request->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $title = $this->t('Move bookmark to folder');
      $response->addCommand(new OpenModalDialogCommand($title, $form, [
        'width' => '75%',
      ]));
      return $response;
    }
    return $form;
  }

  /**
   * Get the label for a node's type.
   *
   * @param $typeId
   *
   * @return string
   */
  private function getNodeTypeLabel($typeId) {
    $labels = &drupal_static(__FUNCTION__, []);
    if (!isset($labels[$typeId])) {
      $type = $this->nodeTypeStorage()->load($typeId);
      $labels[$typeId] = $type->label();
    }
    return $labels[$typeId];
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

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  private function nodeTypeStorage() {
    if (empty($this->nodeTypeStorage)) {
      $this->nodeTypeStorage = $this->entityTypeManager()->getStorage('node_type');
    }
    return $this->nodeTypeStorage;
  }
}
