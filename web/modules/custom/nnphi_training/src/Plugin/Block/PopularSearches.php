<?php

namespace Drupal\nnphi_training\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PopularSearches
 *
 * @package Drupal\nnphi_training\Plugin\Block
 *
 * @Block(
 *   id = "popular_searches",
 *   category=@Translation("Training"),
 *   admin_label=@Translation("Popular searches")
 * )
 */
class PopularSearches extends BlockBase implements ContainerFactoryPluginInterface {

  const STATE_KEY = 'nnphi_training_popular_searches';

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['limit'] = 6;
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Links to show'),
      '#description' => $this->t('The number of links to show'),
      '#default_value' => $this->configuration['limit'],
      '#max' => 20,
      '#min' => 1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['limit'] = $form_state->getValue('limit');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [
      '#cache' => [
        'tags' => [self::STATE_KEY],
      ],
    ];
    $searches = $this->state->get(self::STATE_KEY);
    if (empty($searches)) {
      return $output;
    }
    $searches = array_slice($searches, 0, $this->configuration['limit']);
    $links = [];
    foreach ($searches as $search) {
      $links[] = [
        'title' => $search,
        'url' => Url::fromRoute('nnphi_training.instant_search', [], [
          'query' => ['q' => $search],
        ]),
      ];
    }
    $output['links'] = [
      '#theme' => 'links',
      '#attributes' => [
        'class' => ['searches'],
      ],
      '#links' => $links,
    ];
    return $output;
  }

}
