<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Bookmark folder entities.
 *
 * @ingroup nnphi_bookmark
 */
class BookmarkFolderDeleteForm extends ContentEntityDeleteForm {
  public function getRedirectUrl() {
    return Url::fromRoute('nnphi_bookmark.user_list', ['user' => $this->entity->getOwnerId()]);
  }

}
