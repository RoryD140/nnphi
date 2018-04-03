<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "user_new_trainings",
 *   admin_label = @Translation("New trainings"),
 *   category = @Translation("User"),
 *   context = {
 *   "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *  }
 * )
 */
class NewTrainings extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected $userData;

  const USER_DATA_KEY = 'last_logout';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager,
                              UserDataInterface $userData) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->userData = $userData;
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
      $container->get('user.data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#prefix'] = '<div class="block-content">';
    $build['#suffix'] = '</div>';
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->getContextValue('user');
    $time = $this->userData->get('nnphi_user', $account->id(), self::USER_DATA_KEY);
    $nids = [];
    if ($time) {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $nids = $node_storage->getQuery()
        ->condition('type', 'training')
        ->condition('status', NodeInterface::PUBLISHED)
        ->condition('created', $time, '>')
        ->range(0, 5)
        ->sort('created', 'DESC')
        ->execute();
    }
    if (empty($nids)) {
      $build['empty'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="block-content">',
        '#markup' => $this->t('There are no new trainings to view.'),
        '#suffix' => '</div>',
      ];
    }
    else {
      $nodes = $node_storage->loadMultiple($nids);
      $build['content'] = $this->entityTypeManager->getViewBuilder('node')->viewMultiple($nodes, 'mini');
    }
    return $build;
  }

}
