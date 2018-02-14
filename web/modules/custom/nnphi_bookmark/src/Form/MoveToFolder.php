<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\flag\FlaggingInterface;
use Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface;

class MoveToFolder extends AddToFolder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bookmark_folder_move_to_folder';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, FlaggingInterface $flagging = NULL,
                            BookmarkFolderInterface $from = NULL) {
    $form['folder'] = [
      '#type' => 'value',
      '#value' => $from,
    ];

    $form = parent::buildForm($form, $form_state, $flagging);

    $form['actions']['submit']['#value'] = $this->t('Move to Selected');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->folderService->removeBookmarkFromFolder($form_state->getValue('bookmark'), $form_state->getValue('folder'));
  }

  /**
   * {@inheritdoc}
   */
  public function cancelSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.bookmark_folder.canonical', ['bookmark_folder' => $form_state->getValue('folder')->id()]);
  }

}
