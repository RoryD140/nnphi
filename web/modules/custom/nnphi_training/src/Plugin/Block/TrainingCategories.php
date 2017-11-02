<?php

namespace Drupal\nnphi_training\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Class TrainingCategories
 *
 * @package Drupal\nnphi_training\Plugin\Block
 *
 * @Block(
 *   id="training_categories",
 *   category=@Translation("Training"),
 *   admin_label=@Translation("Training categories")
 * )
 */
class TrainingCategories extends BlockBase implements ContainerFactoryPluginInterface {

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
  public function build() {
    $build = [];
    $keywords = $this->configuration['keywords'];
    $count = count($keywords);
    // Break the keyword list into columns.
    if ($count > 4) {
      $chunk_length = round($count/2);
      $keyword_lists = array_chunk($keywords, $chunk_length);
    }
    else {
      $keyword_lists = [$keywords];
    }
    foreach ($keyword_lists as $delta => $keyword_list) {
      $links = [];
      foreach ($keyword_list as $keyword) {
        $links[] = [
          'title' => $keyword,
          'url' => Url::fromRoute('nnphi_training.instant_search', [], [
            'query' => ['q' => $keyword],
          ]),
        ];
      }
      $build['keywords'][$delta] = [
        '#theme' => 'links',
        '#links' => $links,
      ];
    }
    $build['#cache']['max-age'] = Cache::PERMANENT;
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $default = '';
    if (!empty($this->configuration['keywords'])) {
      $default = implode("\n", $this->configuration['keywords']);
    }
    $form['keywords'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#description' => $this->t('Enter a list of keywords, one on each line.'),
      '#title' => $this->t('Keywords'),
      '#default_value' => $default,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $vals = explode("\n", $form_state->getValue('keywords'));
    $form_state->setValue('keywords', $vals);
    parent::validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['keywords'] = $form_state->getValue('keywords');
    parent::submitConfigurationForm($form, $form_state);
  }
}
