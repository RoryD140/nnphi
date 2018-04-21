<?php

namespace Drupal\nnphi_micro_assessment\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "random_quiz",
 *   admin_label = @Translation("Random Quiz (Micro Assessment)"),
 *   category = @Translation("Assessment")
 * )
 */
class RandomQuiz extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $id = Html::getUniqueId('random-quiz');
    $build = [];
    $build['#attached']['library'][] = 'nnphi_micro_assessment/random_quiz';
    $build['markup'] = [
      '#type' => 'inline_template',
      '#template' => "<div id='$id' class='quiz-wrapper random-quiz'><span class='micro-assessment-close-btn'></span></div>",
    ];
    return $build;
  }

}
