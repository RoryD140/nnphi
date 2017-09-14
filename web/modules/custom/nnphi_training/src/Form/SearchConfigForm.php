<?php

namespace Drupal\nnphi_training\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SearchConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['nnphi_training.search.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nnphi_search_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nnphi_training.search.config');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search only API key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
      '#description' => $this->t('Use the "Search API Key" from the Algolia UI. Do NOT use the same key used for indexing.'),
    ];

    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $config->get('app_id'),
      '#required' => TRUE,
    ];

    $form['index'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Algolia index'),
      '#default_value' => $config->get('index'),
      '#required' => TRUE,
    ];

    return $form += parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('nnphi_training.search.config');
    $config->setData([
      'api_key' => $form_state->getValue('api_key'),
      'app_id' => $form_state->getValue('app_id'),
      'index' => $form_state->getValue('index'),
    ]);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
