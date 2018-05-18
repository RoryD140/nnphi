<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "user_new_trainings",
 *   admin_label = @Translation("New training"),
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
    if ($nodes = $this->getNodes()) {
      $build['content'] = $this->entityTypeManager->getViewBuilder('node')->viewMultiple($nodes, 'mini');
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // In case a user is logged out due to inactivity,
    // limit this to 4 hours (60*60*4).
    return Cache::mergeMaxAges(14400, parent::getCacheMaxAge());
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    $this->nodeQuery($account);
    return AccessResult::allowedIf(is_array($this->getNodes()));
  }

  /**
   * @return false|NodeInterface[]
   */
  protected function getNodes() {
    return $this->nodes;
  }

  /**
   * @param $nodes
   */
  protected function setNodes($nodes) {
    $this->nodes = $nodes;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  private function nodeQuery(AccountInterface $account) {
    $time = $this->userData->get('nnphi_user', $account->id(), self::USER_DATA_KEY);
    $max_age = strtotime('-4 hours');
    if (!$time || $time < $max_age) {
      $time = $max_age;
    }
    $node_storage = $this->entityTypeManager->getStorage('node');
    $nids = $node_storage->getQuery()
      ->condition('type', 'training')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('created', $time, '>')
      ->range(0, 5)
      ->sort('created', 'DESC')
      ->execute();
    if (empty($nids)) {
      $this->setNodes(FALSE);
    }
    else {
      $nodes = $node_storage->loadMultiple($nids);
      $this->setNodes($nodes);
    }
  }

}
