<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\flag\FlagLinkBuilderInterface;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * @Block(
 *  id="bookmarked_trainings",
 *  category=@Translation("User"),
 *  admin_label=@Translation("Bookmarked trainings"),
 *  context = {
 *   "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *  }
 * )
 */
class BookmarkedTrainings extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\flag\Entity\Storage\FlaggingStorageInterface
   */
  protected $flaggingStorage;

  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewer;

  /**
   * @var \Drupal\flag\FlagLinkBuilderInterface
   */
  protected $flagLinkBuilder;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager,
                              FlagLinkBuilderInterface $flagLinkBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->flaggingStorage = $entityTypeManager->getStorage('flagging');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->nodeViewer = $entityTypeManager->getViewBuilder('node');
    $this->flagLinkBuilder = $flagLinkBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('flag.link_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\Core\Session\AccountInterface $account */
    $account = $this->getContextValue('user');
    $flagging_ids = $this->flaggingStorage->getQuery()
      ->condition('uid', $account->id())
      ->condition('flag_id', 'bookmark')
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->execute();
    if (empty($flagging_ids)) {
      // Empty content.
      $build['empty'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="block-content">',
        '#markup' => $this->t('You have not bookmarked any trainings.'),
        '#suffix' => '</div>',
      ];
    }
    else {
      $build['nodes'] = [];
      /** @var \Drupal\flag\FlaggingInterface[] $flaggings */
      $flaggings = $this->flaggingStorage->loadMultiple($flagging_ids);
      $build = [];
      $nids = [];
      foreach ($flaggings as $flagging) {
        $nids[] = $flagging->entity_id->value;
      }
      /** @var \Drupal\node\NodeInterface[] $nodes */
      $nodes = $this->nodeStorage->loadMultiple($nids);
      $build['nodes']['#prefix'] =  '<div class="training-bookmarks">';
      $build['nodes']['#suffix'] =  '</div>';
      foreach ($nodes as $node) {
        $build['nodes'][$node->id()] = [
          '#prefix' => '<div class="training-bookmark">',
          'display' => $this->nodeViewer->view($node, 'mini'),
          'flag' => $this->flagLinkBuilder->build('node', $node->id(), 'bookmark'),
          '#suffix' => '</div>',
        ];
        $build['nodes'][$node->id()]['flag']['#attributes']['class'][] = 'unflag-link';
      }
      $build['#attached']['library'][] = 'nnphi_user/bookmarks';
      $build['nodes']['manage_bookmarks'] = [
        '#title' => $this->t('View and Manage Bookmarks'),
        '#type' => 'link',
        '#url' => Url::fromRoute('nnphi_bookmark.user_list', ['user' => $account->id()]),
        '#attributes' => [
          'class' => ['btn', 'btn-outline-primary', 'btn-sm'],
        ],
      ];
    }
    // Expensive rendering (nodes and flags) is cached,
    // but caching this block for every user would be counterproductive.
    $build['#cache'] = [
      'keys' => [],
      'contexts' => [],
      'tags' => [],
      'max-age' => 0,
    ];
    return $build;
  }
}