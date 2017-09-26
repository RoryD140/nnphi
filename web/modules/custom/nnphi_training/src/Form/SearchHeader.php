<?php

namespace Drupal\nnphi_training\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchHeader extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nnphi_training_search_header';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'training-search-form';

    $form['keys'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Search for trainings'),
      '#title' => $this->t('Search for trainings'),
      '#title_display' => 'invisible',
      '#attributes' => [
        'class' => ['training-search-input'],
        'data-path' => Url::fromRoute('nnphi_training.instant_search')->toString(),
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => [
        'class' => ['js-hide'],
      ],
    ];

    $form['footer_template'] = [
      '#type' => 'markup',
      '#theme' => 'training_autocomplete_footer',
    ];

    $form['#attached']['library'][] = 'nnphi_training/autocomplete';
    $form['#attached']['drupalSettings']['trainingSearch'] = nnphi_training_search_js_settings();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $keys = $form_state->getValue('keys');
    $form_state->setRedirect('nnphi_training.instant_search', [], ['query' => ['q' => $keys]]);
  }

}