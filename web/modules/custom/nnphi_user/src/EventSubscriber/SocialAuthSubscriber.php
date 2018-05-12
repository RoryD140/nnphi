<?php

namespace Drupal\nnphi_user\EventSubscriber;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SocialAuthSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   */
  public function __construct(SocialAuthDataHandler $authDataHandler) {
    $this->authDataHandler = $authDataHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::RESPONSE] = 'onResponse';
    return $events;
  }

  /**
   * Kernel response
   */
  public function onResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    if (!$response instanceof TrustedRedirectResponse) {
      return;
    }
    $hosts = [
      'www.linkedin.com',
      'www.facebook.com',
      'www.google.com',
    ];
    $url = $response->getTargetUrl();
    $parsed = parse_url($url);
    if (empty($parsed['host']) || !in_array($parsed['host'], $hosts)) {
      return;
    }
    $request = $event->getRequest();
    $bm = $request->get('bookmark');
    if (empty($bm) || !is_numeric($bm))  {
      return;
    }
    // Store the bookmark in the session data.
    $this->authDataHandler->set('bookmark', $bm);
  }

}
