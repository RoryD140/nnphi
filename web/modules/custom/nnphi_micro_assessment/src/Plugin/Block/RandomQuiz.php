<?php

namespace Drupal\nnphi_micro_assessment\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Block(
 *   id = "random_quiz",
 *   admin_label = @Translation("Random Quiz (Micro Assessment)"),
 *   category = @Translation("Assessment")
 * )
 */
class RandomQuiz extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
            'white_background' => 0,
        ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['white_background'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('White Background'),
        '#description' => $this->t('Check to enable white background'),
        '#default_value' => $this->configuration['white_background'],
        '#weight' => '0',
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['white_background'] = $form_state->getValue('white_background');
    parent::submitConfigurationForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    $id = Html::getUniqueId('random-quiz');
    $build = [];
    $build['#attached']['library'][] = 'nnphi_micro_assessment/random_quiz';
    $build['#attributes']['class'][] = $this->configuration['white_background'] === 1 ? 'white-bg' : 'gradient-bg';
    $build['markup'] = [
      '#type' => 'inline_template',
      '#template' => "<div id='$id' class='quiz-wrapper random-quiz js-pre-ajax'></div>",
    ];
    return $build;
  }

}
