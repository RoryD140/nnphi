<?php

namespace Drupal\nnphi_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\social_api\SocialApiDataHandler;
use Drupal\social_auth\SocialAuthUserManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SocialLogin extends ControllerBase {

  /**
   * @var \Drupal\social_api\SocialApiDataHandler
   */
  protected $apiDataHandler;

  /**
   * @var \Drupal\social_auth\SocialAuthUserManager
   */
  protected $userManager;

  public function __construct(SocialApiDataHandler $apiDataHandler, SocialAuthUserManager $authUserManager) {
    $this->apiDataHandler = $apiDataHandler;
    $this->userManager = $authUserManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('social_auth.data_handler'),
      $container->get('social_auth.user_manager')
    );
  }

  /**
   * Wrapper controller around social_auth.
   *
   * @param string $service
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function login($service) {
    $services = [
      'facebook' => [
        'route' => 'social_auth_facebook.redirect_to_fb',
        'plugin' => 'social_auth_facebook',
      ],
      'linkedin' => [
        'route' => 'social_auth_linkedin.redirect_to_linkedin',
        'plugin' => 'social_auth_linkedin',
      ],
      'google' => [
        'route' => 'social_auth_google.redirect_to_google',
        'plugin' => 'social_auth_google',
      ]
    ];
    if (!isset($services[$service])) {
      throw new NotFoundHttpException();
    }
    $service_info = $services[$service];
    // Instantiate the session manager.
    $this->userManager->setPluginId($service_info['plugin']);
    // Allow modules to add data to the session before sending for
    // authentication.
    $data = $this->moduleHandler()->invokeAll('social_login_data', [$service]);
    if (is_array($data) && !empty($data)) {
      foreach ($data as $key => $value) {
        $this->apiDataHandler->set($key, $value);
      }
      // Clear any keys added if authentication fails.
      $this->userManager->setSessionKeysToNullify(array_keys($data));
    }
    // Redirect to the appropriate module.
    return $this->redirect($service_info['route']);
  }

}