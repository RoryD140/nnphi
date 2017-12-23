<?php

namespace Drupal\nnphi_curriculum\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EnrollmentController extends ControllerBase {

  public function __construct() {
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nnphi_curriculum.progress')
    );
  }

  public function curriculum(NodeInterface $node) {

  }
}