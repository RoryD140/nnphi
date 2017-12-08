<?php

namespace Drupal\nnphi_bookmark;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Bookmark folder entities.
 *
 * @ingroup nnphi_bookmark
 */
class BookmarkFolderListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Bookmark folder ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\nnphi_bookmark\Entity\BookmarkFolder */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.bookmark_folder.canonical',
      ['bookmark_folder' => $entity->id(), 'user' => $entity->getOwnerId()]
    );
    return $row + parent::buildRow($entity);
  }

}
