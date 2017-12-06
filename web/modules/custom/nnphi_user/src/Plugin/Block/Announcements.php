<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a 'Announcements' block.
 *
 * @Block(
 *  id = "announcements",
 *  category = @Translation("User"),
 *  admin_label = @Translation("Announcements"),
 * )
 */
class Announcements extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Constructs a new Announcements object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        EntityTypeManager $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['content'] = $this->getContent();

    $build['#cache'] = [
      'contexts' => [],
      'keys' => ['announcement_list'],
      'tags' => ['node_list'],
      'max-age' => Cache::PERMANENT,
    ];

    return $build;
  }

  protected function getContent() {
    $node_storage = $this->entityTypeManager->getStorage('node');
    // Load the 3 most recent announcement nodes.
    $nids = $node_storage->getQuery()
      ->condition('type', 'announcement')
      ->condition('status', NodeInterface::PUBLISHED)
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->execute();
    if (!$nids) {
      return ['#markup' => '<div class="empty">' . $this->t('There are no current annoucements.') . '</div>'];
    }
    $node_viewer = $this->entityTypeManager->getViewBuilder('node');
    $nodes = $node_storage->loadMultiple($nids);
    return $node_viewer->viewMultiple($nodes, 'teaser');
  }

}
