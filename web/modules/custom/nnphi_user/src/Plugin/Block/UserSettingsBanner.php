<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Class UserBanner
 * @package Drupal\nnphi_user\Plugin\Block
 *
 * @Block(
 *  id="user_settings_banner",
 *  category=@Translation("User"),
 *  admin_label=@Translation("User settings banner"),
 * )
 */
class UserSettingsBanner extends BlockBase {

  public function build() {
    $build = [
      '#type' => 'markup',
      '#prefix' => '<div class="container">',
      '#markup' => '<h1>' . $this->t('Settings') . '</h1>',
      '#suffix' => '</div>',
    ];
    $build['#cache']['keys'] = ['user_settings_banner'];
    $build['#cache']['max-age'] = Cache::PERMANENT;
    return $build;
  }
}