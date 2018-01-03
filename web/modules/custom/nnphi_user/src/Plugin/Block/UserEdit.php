<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Profile Block.
 *
 * @Block(
 *  id = "user_edit",
 *  admin_label = @Translation("User Profile Edit Block"),
 * )
 */
class UserEdit extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFormBuilderInterface $entity_form_builder, AccountInterface $current_user, EntityStorageInterface $user_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityFormBuilder = $entity_form_builder;
    $this->currentUser = $current_user;
    $this->userStorage = $user_storage;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.form_builder'),
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('user')
    );
  }

  public function build() {
    $user = $this->userStorage->load($this->currentUser->id());
    return $this->entityFormBuilder->getForm($user);
    CacheableMetadata::createFromObject($account)
      ->applyTo($build);
    $build['#cache']['keys'] = ['user', 'user_edit', $account->id()];
    return $build;
  }

}