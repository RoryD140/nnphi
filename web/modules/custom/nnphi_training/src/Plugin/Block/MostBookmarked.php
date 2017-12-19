<?php

namespace Drupal\nnphi_training\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Element;

/**
 * @Block(
 *   id="most_bookmarked",
 *   category=@Translation("Training"),
 *   admin_label=@Translation("Most Bookmarked"),
 * )
 */
class MostBookmarked extends BlockBase implements ContainerFactoryPluginInterface {

  private $connection;

  private $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection,
                              EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $nids = $this->popularQuery();
    $build = [];
    if (empty($nids)) {
      $build['#cache'] = [
        'contexts' => [],
        'keys' => [],
        'max-age' => 900,
      ];
      return $build;
    }
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $nodes = $this->entityTypeManager->getViewBuilder('node')->viewMultiple($nodes, 'teaser');
    foreach (Element::children($nodes) as $nid) {
      $nodes[$nid]['#prefix'] = '<div>';
      $nodes[$nid]['#suffix'] = '</div>';
    }

//    $build['#attached']['library'][] = 'nnphi_training/slider';

    $build['#prefix'] = '<section class="training-related">';
    $build['#suffix'] = '</section>';

    $build['nodes'] = [
      '#prefix' => '<div class="slick-slider">',
      '#suffix' => '</div>',
      'nodes' => $nodes,
    ];

    $build['#cache'] = [
      'max-age' => 900,
      'contexts' => [],
      'keys' => ['node', 'teaser'] + array_values($nids),
      'tags' => ['node_list'],
    ];

    return $build;
  }

  /**
   * Get a list of bookmarked NIDs.
   *
   * @return mixed
   */
  private function popularQuery() {
    $nids = $this->connection->queryRange("SELECT fc.entity_id FROM {flag_counts} fc
                                    INNER JOIN {node_field_data} n ON n.nid = fc.entity_id
                                    WHERE n.type = 'training'
                                    AND fc.flag_id = 'bookmark'
                                    AND n.status = :np
                                    ORDER BY fc.count DESC", 0, 20, [':np' => NodeInterface::PUBLISHED])->fetchCol();
    return $nids;
  }

}