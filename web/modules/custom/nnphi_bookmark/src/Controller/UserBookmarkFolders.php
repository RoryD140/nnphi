<?php

namespace Drupal\nnphi_bookmark\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\flag\FlagServiceInterface;
use Drupal\nnphi_bookmark\BookmarkFolderService;
use Drupal\nnphi_bookmark\Form\ManageBookmarks;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserBookmarkFolders extends ControllerBase {

  private $folderService;

  public function __construct(BookmarkFolderService $folderService, FlagServiceInterface $flagService) {
    $this->folderService = $folderService;
    $this->flagService = $flagService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nnphi_bookmark.folder'),
      $container->get('flag')
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
    $build['bookmarks'] = $this->getUserBookmarks($user);
    $build['folders'] = $this->getUserFolders($user);

    // Add user metadata to cache array.
    CacheableMetadata::createFromObject($user)
      ->applyTo($build);
    $build['#cache']['keys'] = ['user', 'user_bookmark_folder_list', $user->id()];
    $build['#attached']['library'][] = 'nnphi_bookmark/manage_bookmarks';
    $build['#attached']['library'][] = 'core/drupal.ajax';
    $build['#attached']['library'][] = 'core/drupal.dialog.ajax';

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
      'name' => $this->t('Name'),
      'opts' => '',
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
      $rows[] = [
        ['data-sort' => $folder->label(), 'data' => $folder->toLink($folder->label())],
        '',
      ];
    }

    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => [
        'class' => ['user-bookmarks-table'],
      ]
    ];

//    $build['#cache']['keys'] = ['user', 'bookmark_folders', $user->id()];

    return $build;
  }

  /**
   * Get
   * @param \Drupal\user\UserInterface $user
   *
   * @return bool|\Drupal\flag\FlaggingInterface[]
   */
  private function getUserBookmarks(UserInterface $user) {
    $build = [];
    $build['header'] = [
      'link' => [
        '#type' => 'link',
        '#title' => $this->t('Add Folder'),
        '#url' => Url::fromRoute('nnphi_bookmark.add_folder', ['ajax' => 'nojs']),
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
        ],
      ],
    ];
    $build['form'] = $this->formBuilder()->getForm(ManageBookmarks::class, $user);
    return $build;
  }

  public function addFolder($ajax = 'ajax') {
    $folder = $this->entityTypeManager()->getStorage('bookmark_folder')
      ->create(['uid' => $this->currentUser()->id()]);
    $form = $this->entityFormBuilder()->getForm($folder);
    if ($ajax === 'ajax') {
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand($this->t('Add Folder'), $form, [
        'width' => '40%',
      ]));
      return $response;
    }
    return $form;
  }
}
