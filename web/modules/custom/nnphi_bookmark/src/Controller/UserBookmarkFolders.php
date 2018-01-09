<?php

namespace Drupal\nnphi_bookmark\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
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
//    $folders = $this->folderService->getFoldersForUser($user);

//    if (empty($folders)) {
//      $build['empty'] = [
//        '#type' => 'markup',
//        '#markup' => $this->t('You have not created any bookmark folders yet.'),
//      ];
//    }
//    else {
//      $build['folders'] = $this->entityTypeManager()->getViewBuilder('bookmark_folder')->viewMultiple($folders);
//    }
//    // Add user metadata to cache array.
//    CacheableMetadata::createFromObject($user)
//      ->applyTo($build);
//    $build['#cache']['keys'] = ['user', 'user_bookmark_folder_list', $user->id()];
//    $build['#cache']['contexts'] = [];
//    $build['#cache']['tags'][] = 'user_bookmark_folders:' . $user->id();

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

  /**
   * Get
   * @param \Drupal\user\UserInterface $user
   *
   * @return bool|\Drupal\flag\FlaggingInterface[]
   */
  private function getUserBookmarks(UserInterface $user) {
    return $this->formBuilder()->getForm(ManageBookmarks::class, $user);
    $ns = $this->entityTypeManager()->getStorage('node');
    $fs = $this->entityTypeManager()->getStorage('flagging');
    $fids = $fs->getQuery()
      ->condition('flag_id', 'bookmark')
      ->condition('uid', $user->id())
      ->notExists('field_bookmark_folder')
      ->sort('created', 'DESC')
      ->execute();
    if (empty($fids)) {
      return FALSE;
    }

    $options = [];

    /** @var \Drupal\flag\FlaggingInterface $flag */
    foreach ($fs->loadMultiple($fids) as $flag) {
      $nid = $flag->get('entity_id')->getString();
      $node = $ns->load($nid);
      $rating = '';
      if ($node->hasField('field_training_overall_rating') && $node->get('field_training_overall_rating')->count()) {
        $field = $node->get('field_training_overall_rating');
        $rating = $this->entityTypeManager()->getViewBuilder('node')->viewField($field, 'full');
      }
      $options[$flag->id()] = [
        'select' => '',
        'name' => $node->toLink($node->label()),
        'date' => $flag->get('created')->getString(),
        'rating' => '',
        'options' => 'opts',
      ];
    }

    $header = [
      'select' => ['data' => ''],
      'name' => $this->t('Name'),
      'date' => $this->t('Date Added'),
      'rating' => $this->t('Rating'),
    ];

    $debug = TRUE;

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $options,
    ];
  }
}
