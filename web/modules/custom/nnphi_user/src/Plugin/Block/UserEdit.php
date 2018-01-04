<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Profile Block.
 *
 * @Block(
 *  id="user_edit",
 *  category=@Translation("User"),
 *  admin_label=@Translation("User edit form"),
 *  context = {
 *   "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *  }
 * )
 */
class UserEdit extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFormBuilderInterface $entity_form_builder, EntityStorageInterface $user_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityFormBuilder = $entity_form_builder;
    $this->userStorage = $user_storage;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.form_builder'),
      $container->get('entity_type.manager')->getStorage('user')
    );
  }

  public function build() {
    $account = $this->getContextValue('user');
    $build['form'] = $this->entityFormBuilder->getForm($account);
    CacheableMetadata::createFromObject($account)
      ->applyTo($build);
    $build['#cache']['keys'] = ['user', 'user_edit', $account->id()];
    return $build;
  }

}