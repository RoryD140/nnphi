<?php

namespace Drupal\nnphi_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\nnphi_training\TrainingSuggestions as RecommendationService;

/**
 * Class TrainingSuggestions
 *
 * @package Drupal\nnphi_user\Plugin\Block
 *
 * @Block(
 *  id="user_training_suggestions",
 *  category=@Translation("User"),
 *  admin_label=@Translation("User training suggestions"),
 *  context = {
 *   "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *  }
 * )
 */
class TrainingSuggestions extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\nnphi_training\TrainingSuggestions
   */
  private $recommendationService;

  /**
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  private $nodeViewer;

  /**
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  private $redirectService;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, RecommendationService $trainingSuggestions,
                              EntityTypeManagerInterface $entityTypeManager, RedirectDestinationInterface $redirectDestination) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->recommendationService = $trainingSuggestions;
    $this->nodeViewer = $entityTypeManager->getViewBuilder('node');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
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
      $container->get('nnphi_training.suggestions'),
      $container->get('entity_type.manager'),
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#prefix'] = '<div class="block-content">';
    $build['#suffix'] = '</div>';

    $account = $this->getContextValue('user');
    if (!$this->canSuggest($account)) {
      $build['content'] = $this->completeProfileContent($account);
    }
    else {
      /** @var \Drupal\node\NodeInterface[] $nodes */
      $nodes = $this->recommendationService->getSuggestionsForUser($account);
      if (empty($nodes)) {
        $build['content'] = $this->emptyContent($account);
      }
      else {
        $nodes = array_slice($nodes, 0, 3);
        $build['content'] = $this->nodeViewer->viewMultiple($nodes, 'mini');
        $build['link'] = [
          '#prefix' => '<div>',
          '#suffix' => '</div>',
          '#type' => 'link',
          '#title' => $this->t('View More'),
          '#url' => Url::fromRoute('nnphi_user.suggestions', ['user' => $account->id()]),
        ];
      }
    }

    CacheableMetadata::createFromObject($account)
      ->applyTo($build);

    $build['#cache']['max-age'] = 900;
    $build['#cache']['contexts'] = [];
    $build['#cache']['keys'] = ['user', 'user_training_suggestions', $account->id()];

    return $build;
  }

  /**
   * Content for when the user needs to complete their profile.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return mixed
   */
  protected function completeProfileContent(AccountInterface $account) {
    $node = $this->nodeStorage->getQuery()
      ->condition('type', 'training')
      ->range(0, 1)
      ->sort('created', 'DESC')
      ->execute();
    $node = $this->nodeStorage->load(end($node));
    $empty['sample'] = [
      '#prefix' => '<div class="node-preview-disabled">',
      '#suffix' => '</div>',
      'node' => $this->nodeViewer->view($node, 'mini'),
    ];

    $empty['link'] = [
      '#title' => $this->t('Complete your profile to view selected trainings'),
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.user.edit_form', ['user' => $account->id()], ['query' => ['destination' => $this->redirectService->get()]]),
    ];

    return $empty;
  }

  /**
   * Determine if an account has filled out enough of its profile
   *  to make training suggestions.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool
   */
  protected function canSuggest(AccountInterface $account) {
    $interests = $account->get('field_user_interests');
    $job_title = $account->get('field_user_job_title');
    if ($interests->isEmpty() && $job_title->isEmpty()) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Content for when there are no suggested trainings available.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return array
   */
  protected function emptyContent(AccountInterface $account) {
    return [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('There are no suggested trainings available. Please check back again later.') . '</p>',
    ];
  }
}
