<?php

namespace Drupal\nnphi_micro_assessment\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\nnphi_micro_assessment\Form\MicroAssessmentForm;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;

class RandomQuiz extends ControllerBase {
  public function ajax(Request $request, NodeInterface $node = NULL) {
    $selector = Html::escape($request->get('selector'));
    if (!$node) {
      $node_storage = $this->entityTypeManager()->getStorage('node');
      $qid = $node_storage
        ->getQuery()
        ->condition('status', NodeInterface::PUBLISHED)
        ->condition('type', 'micro_assessment')
        ->range(0, 1)
        ->addTag('sort_by_random')
        ->execute();
      $node = $node_storage->load(end($qid));
      if (!$node) {
        return $this->emptyResponse($selector);
      }
    }
    $form = $this->formBuilder()->getForm(MicroAssessmentForm::class, $node, $selector);
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#' . $selector, $form));
    return $response;
  }

  protected function emptyResponse($selector) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#' . $selector, $this->t('No assessments available.')));
    return $response;
  }
}
