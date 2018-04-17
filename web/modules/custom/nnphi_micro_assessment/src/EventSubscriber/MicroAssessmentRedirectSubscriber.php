<?php

namespace Drupal\nnphi_micro_assessment\EventSubscriber;

use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MicroAssessmentRedirectSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return([
      KernelEvents::REQUEST => [
        ['redirectMicroAssessment'],
      ]
    ]);
  }

  public function redirectMicroAssessment(GetResponseEvent $event) {
    $request = $event->getRequest();
    if ($request->attributes->get('_route') !== 'entity.node.canonical') {
      return;
    }

    // Only redirect a certain content type.
    /** @var \Drupal\node\NodeInterface $node */
    $node = $request->attributes->get('node');
    if ($node->getType() !== 'micro_assessment') {
      return;
    }

    // Users don't need to see the micro assessment node.
    if (!$node->access('update')) {
      $redirect_url = Url::fromRoute('nnphi_training.instant_search');
      $response = new CacheableRedirectResponse($redirect_url->toString(), 301);
      $event->setResponse($response);
    }

  }

}