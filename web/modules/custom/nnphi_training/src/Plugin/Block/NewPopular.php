<?php

namespace Drupal\nnphi_training\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
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

  protected $nodeViewer;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, NodeStorageInterface $nodeStorage,
                              EntityViewBuilderInterface $entityViewBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $nodeStorage;
    $this->nodeViewer = $entityViewBuilder;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('entity_type.manager')->getViewBuilder('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];
    $output['new'] = [
      'wrapper-open' => [
        '#type' => 'markup',
        '#markup' => '<div class="new-popular-block-interior"><div class="new-trainings-wrapper">',
      ],
      'header' => [
        '#type' => 'markup',
        '#markup' => '<h3>' . $this->t('New Trainings') . '</h3>',
      ],
      'nodes' => $this->getNewTrainings(),
      'wrapper-close' => [
        '#type' => 'markup',
        '#markup' => '</div>',
      ],
    ];
    $output['popular'] = [
      'wrapper-open' => [
        '#type' => 'markup',
        '#markup' => '<div class="popular-trainings-wrapper">',
      ],
      'header' => [
        '#type' => 'markup',
        '#markup' => '<h3>' . $this->t('Popular Trainings') . '</h3>',
      ],
      'nodes' => $this->getPopularTrainings(),
      'wrapper-close' => [
        '#type' => 'markup',
        '#markup' => '</div></div>',
      ],
    ];
    return $output;
  }

  /**
   * Get the 3 most recently created trainings and view them.
   *
   * @return array
   */
  protected function getNewTrainings() {
    // New trainings.
    $query = $this->nodeStorage->getQuery();
    $new_nids = $query
      ->condition('type', 'training')
      ->condition('status', NodeInterface::PUBLISHED)
      ->range(0, 3)
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
    // Placeholder.
    return $this->getNewTrainings();
  }
}
