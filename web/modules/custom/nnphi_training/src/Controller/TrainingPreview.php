<?php

namespace Drupal\nnphi_training\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TrainingPreview extends ControllerBase {

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * TrainingPreview constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  public function preview(NodeInterface $node) {
    if ($node->getType() !== 'training') {
      throw new NotFoundHttpException();
    }
    $view = $this->entityTypeManager()->getViewBuilder('node')
      ->view($node, 'preview');

    $render = $this->renderer->renderRoot($view);

    $return['content'] = $render;

    $response = new CacheableJsonResponse($return);
    $response->addCacheableDependency($node);
    return $response;
  }
}
