<?php

namespace Drupal\nnphi_training\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchHeader extends FormBase {

  /**
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected $twig;

  public function __construct(TwigEnvironment $twig) {
    $this->twig = $twig;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('twig')
    );
  }

  public function getFormId() {
    return 'nnphi_training_search_header';
  }

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

    $form['footer_template'] = [
      '#type' => 'markup',
      '#theme' => 'training_autocomplete_footer',
    ];

    $form['#attached']['library'][] = 'nnphi_training/autocomplete';
    $form['#attached']['drupalSettings']['trainingSearch'] = nnphi_training_search_js_settings();

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

}