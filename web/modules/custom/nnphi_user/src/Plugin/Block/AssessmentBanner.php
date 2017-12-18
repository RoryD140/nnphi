<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AssessmentBanner
 * @package Drupal\nnphi_user\Plugin\Block
 *
 * @Block(
 *  id="user_assessment",
 *  category=@Translation("User"),
 *  admin_label=@Translation("Assessment banner"),
 *  context = {
 *   "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *  }
 * )
 */
class AssessmentBanner extends BlockBase implements ContainerFactoryPluginInterface {

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  public function build() {
    $build = [
      // Todo need to make this a link, but not sure where it points.
      '#markup' => '<h2>' . $this->t('Discover Trainings') . '</h2><h3>' . $this->t('Create Your Individual Learning Plan') .
                '</h3><div class="btn btn-primary">' . $this->t('Assess Your Skills') . '</div>',
      '#prefix' => '<div class="assessment">',
      '#suffix' => '</div>',
    ];
    $build['link'] = [
      '#title' => $this->t('Assess Your Skills'),
      '#type' => 'link',
      // Todo: change this once I know where this goes.
      //'#url' => Url::fromRoute('entity.user.edit_form', ['user' => $account->id()], ['query' => ['destination' => $this->redirectService->get()]]),
    ];
    $data = CacheableMetadata::createFromObject($account);
    $data->applyTo($build);
    $build['#cache']['contexts'][] = 'user';
    return $build;
  }
}