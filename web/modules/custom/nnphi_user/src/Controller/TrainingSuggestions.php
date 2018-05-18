<?php

namespace Drupal\nnphi_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\nnphi_training\TrainingSuggestions as SuggestionService;
use Drupal\Core\Cache\CacheableMetadata;

class TrainingSuggestions extends ControllerBase {

  /**
   * @var \Drupal\nnphi_training\TrainingSuggestions
   */
  protected $trainingSuggestions;

  public function __construct(SuggestionService $trainingSuggestions) {
    $this->trainingSuggestions = $trainingSuggestions;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nnphi_training.suggestions')
    );
  }

  /**
   * User training suggestions page callback.
   *
   * @param \Drupal\user\UserInterface $user
   *
   * @return array
   */
  public function user(UserInterface $user) {
    $build = [];
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $this->trainingSuggestions->getSuggestionsForUser($user);
    if (empty($nodes)) {
      $build['content'] = $this->emptyContent();
    }
    else {
      $build['content'] = $this->entityTypeManager()->getViewBuilder('node')->viewMultiple($nodes, 'teaser');
    }

    CacheableMetadata::createFromObject($user)
      ->applyTo($build);

    $build['#cache']['max-age'] = 900;
    $build['#cache']['contexts'] = [];
    $build['#cache']['keys'] = ['user', 'user_training_suggestions_page', $user->id()];

    return $build;
  }

  /**
   * Content when no suggestions are available.
   *
   * @return array
   */
  protected function emptyContent() {
    return [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('There is no suggested training available. Please check back again later.') . '</p>',
    ];
  }
}