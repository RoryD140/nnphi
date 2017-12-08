<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Bookmark folder edit forms.
 *
 * @ingroup nnphi_bookmark
 */
class BookmarkFolderForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\nnphi_bookmark\Entity\BookmarkFolder */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Bookmark folder.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Bookmark folder.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.bookmark_folder.canonical', ['bookmark_folder' => $entity->id()]);
  }

}
