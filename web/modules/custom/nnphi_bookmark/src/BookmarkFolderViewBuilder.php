<?php

namespace Drupal\nnphi_bookmark;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\nnphi_bookmark\Entity\BookmarkFolder;
use Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookmarkFolderViewBuilder extends EntityViewBuilder {

  protected $entityTypeManager;

  public function __construct(\Drupal\Core\Entity\EntityTypeInterface $entity_type, \Drupal\Core\Entity\EntityManagerInterface $entity_manager, \Drupal\Core\Language\LanguageManagerInterface $language_manager, \Drupal\Core\Theme\Registry $theme_registry = NULL,
                              EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($entity_type, $entity_manager, $language_manager, $theme_registry);
    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('entity_type.manager')
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
    $nids = [];
    $flaggings = $flag_storage->loadMultiple($flaggings);
    foreach ($flaggings as $flagging) {
      $nids[] = $flagging->flagged_entity->target_id;
    }
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadMultiple($nids);
    return [
      'nodes' => $this->entityTypeManager->getViewBuilder('node')->viewMultiple($nodes, 'teaser'),
      'pager' => ['#type' => 'pager'],
    ];
  }

  protected function getEmptyContent() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('You have not saved any trainings in this folder.'),
    ];
  }

}