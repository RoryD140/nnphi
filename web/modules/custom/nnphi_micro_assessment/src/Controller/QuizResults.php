<?php

namespace Drupal\nnphi_micro_assessment\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;

class QuizResults extends ControllerBase {
  public function page(NodeInterface $node) {
    return views_embed_view('micro_assessment_results', 'embed_1', $node->id());
  }

  public function access(NodeInterface $node) {
    return AccessResult::allowedIf($node->getType() === 'micro_assessment');
  }
}
