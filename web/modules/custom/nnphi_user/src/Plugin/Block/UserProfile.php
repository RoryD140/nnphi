<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;

/**
 * Class UserProfile
 * @package Drupal\nnphi_user\Plugin\Block
 *
 * @Block(
 *  id="user_profile",
 *  category=@Translation("User"),
 *  admin_label=@Translation("User profile"),
 *  context = {
 *   "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *  }
 * )
 */
class UserProfile extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  private $userViewer;

  /**
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  private $redirectService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, RedirectDestinationInterface $redirectDestination) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userViewer = $entityTypeManager->getViewBuilder('user');
    $this->redirectService = $redirectDestination;
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
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->getContextValue('user');
    $build = [];
    $build['edit_button'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.user.edit_form', ['user' => $account->id()], ['query' => ['destination' => $this->redirectService->get()]]),
      '#title' => $this->t('Edit'),
    ];
    $build['profile'] = $this->userViewer->view($account, 'profile');
    CacheableMetadata::createFromObject($account)
      ->applyTo($build);
    $edit_access = !self::accountIsComplete($account);
    /** @var \Drupal\Core\Routing\RedirectDestinationInterface $redirect_service */
    $build['edit_link'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.user.edit_form', ['user' => $account->id()], ['query' => ['destination' => $this->redirectService->get()]]),
      '#title' => $this->t('Complete Your Profile'),
      '#access' => $edit_access,
    ];
    CacheableMetadata::createFromObject($account)
      ->applyTo($build);
    $build['#cache']['keys'] = ['user', 'user_profile', $account->id()];
    return $build;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  public static function accountIsComplete(AccountInterface $account) {
    $complete = TRUE;
    $profile_fields = [
      'user_picture',
      'field_user_job_title',
      'field_user_zipcode',
      'field_user_interests',
      'field_user_setting',
      'field_user_role',
    ];
    foreach ($profile_fields as $profile_field) {
      if ($account->get($profile_field)->isEmpty()) {
        $complete = FALSE;
        break;
      }
    }
    return $complete;
  }

}
