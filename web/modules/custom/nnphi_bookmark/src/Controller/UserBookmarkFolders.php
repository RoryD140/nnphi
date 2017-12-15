<?php

namespace Drupal\nnphi_bookmark\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\nnphi_bookmark\BookmarkFolderService;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserBookmarkFolders extends ControllerBase {

  private $folderService;

  public function __construct(BookmarkFolderService $folderService) {
    $this->folderService = $folderService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nnphi_bookmark.folder')
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
    $folders = $this->folderService->getFoldersForUser($user);
    if (empty($folders)) {
      $build['empty'] = [
        '#type' => 'markup',
        '#markup' => $this->t('You have not created any bookmark folders yet.'),
      ];
    }
    else {
      $build['bookmarks'] = $this->entityTypeManager()->getViewBuilder('bookmark_folder')->viewMultiple($folders);
    }
    // Add user metadata to cache array.
    CacheableMetadata::createFromObject($user)
      ->applyTo($build);
    $build['#cache']['keys'] = ['user_bookmark_folder_list', $user->id()];
    $build['#cache']['contexts'] = [];
    $build['#cache']['tags'][] = 'user_bookmark_folders:' . $user->id();

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
}
