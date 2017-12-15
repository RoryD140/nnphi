<?php

namespace Drupal\nnphi_micro_assessment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;

class MicroAssessmentTest extends ControllerBase {
  public function testPage(NodeInterface $node) {
    $build = [];
    $build['node'] = $this->entityTypeManager()->getViewBuilder('node')->view($node, 'teaser');
    $build['form'] = $this->formBuilder()->getForm(\Drupal\nnphi_micro_assessment\Form\MicroAssessmentForm::class, $node);
    return $build;
  }
}