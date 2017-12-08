<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class Test extends FormBase {

  public function getFormId() {
    return 'nnphi_bookmark_test_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $etm = \Drupal::entityTypeManager();
    $fs = $etm->getStorage('flagging');
    $ns = $etm->getStorage('node');
    $flag = $fs->create([
      'uid' => 1,
      'session_id' => NULL,
      'flag_id' => 'bookmark',
      'entity_id' => 24,
      'entity_type' => 'node',
      'global' => FALSE,
    ]);
    $flag->field_bookmark_folder->target_id = 1;
    $flag->save();
  }

}