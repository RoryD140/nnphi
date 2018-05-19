<?php

namespace Drupal\nnphi_training\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewPopular
 *
 * @package Drupal\nnphi_training\Plugin\Block
 *
 * @Block(
 *   id="new_popular_trainings",
 *   category=@Translation("Training"),
 *   admin_label=@Translation("New and popular trainings")
 * )
 */
class NewPopular extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewer;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, NodeStorageInterface $nodeStorage,
                              EntityViewBuilderInterface $entityViewBuilder, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $nodeStorage;
    $this->nodeViewer = $entityViewBuilder;
    $this->database = $database;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('entity_type.manager')->getViewBuilder('node'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];

    $output['#attached']['library'][] = 'nnphi_training/slider';

    $output['new'] = [
      'wrapper-open' => [
        '#type' => 'markup',
        '#markup' => '<div class="new-popular-block-interior"><div class="new-trainings-wrapper">',
      ],
      'header' => [
        '#type' => 'markup',
        '#markup' => '<h3>' . $this->t('New Training') . '</h3><div class="training-vert-slider">',
      ],
      'nodes' => $this->getNewTrainings(),
      'wrapper-close' => [
        '#type' => 'markup',
        '#markup' => '</div></div>',
      ],
    ];
    $output['popular'] = [
      'wrapper-open' => [
        '#type' => 'markup',
        '#markup' => '<div class="popular-trainings-wrapper">',
      ],
      'header' => [
        '#type' => 'markup',
        '#markup' => '<h3>' . $this->t('Popular Training') . '</h3><div class="training-vert-slider">',
      ],
      'nodes' => $this->getPopularTrainings(),
      'wrapper-close' => [
        '#type' => 'markup',
        '#markup' => '</div></div></div>',
      ],
    ];
    return $output;
  }

  /**
   * Get the 10 most recently created trainings and view them.
   *
   * @return array
   */
  protected function getNewTrainings() {
    // New trainings.
    $query = $this->nodeStorage->getQuery();
    $new_nids = $query
      ->condition('type', 'training')
      ->condition('status', NodeInterface::PUBLISHED)
      ->range(0, 10)
      ->sort('created', 'DESC')
      ->execute();
    if (empty($new_nids)) {
      return [
        '#cache' => [
          'tags' => ['node_list'],
        ],
      ];
    }
    $new_trainings = $this->nodeStorage->loadMultiple($new_nids);
    return $this->nodeViewer->viewMultiple($new_trainings, 'mini');
  }

  /**
   * @return array
   */
  protected function getPopularTrainings() {
    // Popular trainings according to flag count.
    $nids = $this->database->queryRange("SELECT fc.entity_id FROM {flag_counts} fc
                                              INNER JOIN {node_field_data} n ON n.nid = fc.entity_id
                                              WHERE entity_type = 'node'
                                              AND flag_id = 'bookmark'
                                              AND n.type = 'training'
                                              AND n.status = 1
                                              ORDER BY fc.count DESC", 0, 10)->fetchCol();
    if (empty($nids)) {
      return [
        '#cache' => [
          'tags' => ['node_list'],
        ],
      ];
    }
    $popular = $this->nodeStorage->loadMultiple($nids);
    return $this->nodeViewer->viewMultiple($popular, 'mini');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // Cache for 15 minutes max.
    return 900;
  }
}
