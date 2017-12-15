<?php

namespace Drupal\nnphi_bookmark\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Bookmark folder entities.
 *
 * @ingroup nnphi_bookmark
 */
interface BookmarkFolderInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Bookmark folder name.
   *
   * @return string
   *   Name of the Bookmark folder.
   */
  public function getName();

  /**
   * Sets the Bookmark folder name.
   *
   * @param string $name
   *   The Bookmark folder name.
   *
   * @return \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface
   *   The called Bookmark folder entity.
   */
  public function setName($name);

  /**
   * Gets the Bookmark folder creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Bookmark folder.
   */
  public function getCreatedTime();

  /**
   * Sets the Bookmark folder creation timestamp.
   *
   * @param int $timestamp
   *   The Bookmark folder creation timestamp.
   *
   * @return \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface
   *   The called Bookmark folder entity.
   */
  public function setCreatedTime($timestamp);

}
