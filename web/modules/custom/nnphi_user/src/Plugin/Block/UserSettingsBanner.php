<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserBanner
 * @package Drupal\nnphi_user\Plugin\Block
 *
 * @Block(
 *  id="user_settings_banner",
 *  category=@Translation("User"),
 *  admin_label=@Translation("User settings banner"),
 *  context = {
 *   "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *  }
 * )
 */
class UserSettingsBanner extends BlockBase implements ContainerFactoryPluginInterface {

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  public function build() {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->getContextValue('user');
    $name = $account->getDisplayName();
    $build = [
      '#type' => 'html_tag',
      '#tag' => 'h1',
      '#value' => $this->t('Settings'),
      '#prefix' => '<div class="container">',
      '#suffix' => '</div>',
    ];
    $data = CacheableMetadata::createFromObject($account);
    $data->applyTo($build);
    $build['#cache']['keys'] = ['user', 'user_settings_banner', $account->id()];
    return $build;
  }
}