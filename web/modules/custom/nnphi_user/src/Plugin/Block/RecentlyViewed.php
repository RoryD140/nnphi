<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RecentlyViewed
 *
 * @package Drupal\nnphi_user\Plugin\Block
 *
 * @Block(
 *  id="user_recently_viewed",
 *  category=@Translation("User"),
 *  admin_label=@Translation("User recently viewed"),
 *  context = {
 *   "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *  }
 * )
 */
class RecentlyViewed extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Database\Database
   */
  private $db;

  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  private $nodeViewer;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection,
                              EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->db = $connection;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->nodeViewer = $entityTypeManager->getViewBuilder('node');
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

  public function build() {
    $build = [];
    $build['#attached']['library'][] = 'nnphi_training/slider';

    // Get the user's history.
    $result = $this->db->query("SELECT nid FROM {history} WHERE uid = :uid ORDER BY timestamp DESC", [
      ':uid' => $this->getContextValue('user')->id(),
    ]);
    if (empty($result)) {
      $build['empty'] = [
        '#type' => 'markup',
        '#markup' => $this->t('You have not viewed any trainings recently.'),
      ];

      return $build;
    }

    $nids = $result->fetchCol();
    $nodes = $this->nodeStorage->loadMultiple($nids);

    $nodes = $this->nodeViewer->viewMultiple($nodes, 'teaser');
    foreach (Element::children($nodes) as $nid) {
      $nodes[$nid]['#prefix'] = '<div>';
      $nodes[$nid]['#suffix'] = '</div>';
    }

//    $build['#attached']['library'][] = 'nnphi_training/slider';

    $build['#prefix'] = '<section class="training-related">';
    $build['#suffix'] = '</div>';

    $build['header'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#attributes' => ['class' => ['training-subtitle']],
      '#value' => $this->t('Recently Viewed Trainings'),
    ];

    $build['nodes'] = [
      '#prefix' => '<div class="slick-slider">',
      '#suffix' => '</div>',
      'nodes' => $nodes,
    ];

    $build['#cache'] = [
      'max-age' => 0,
      'contexts' => ['user'],
      'tags' => [],
      'keys' => ['user_recently_viewed'],
    ];

    return $build;
  }

}