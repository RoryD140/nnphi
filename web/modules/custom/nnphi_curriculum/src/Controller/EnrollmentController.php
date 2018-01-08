<?php

namespace Drupal\nnphi_curriculum\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\nnphi_curriculum\CurriculumLinkBuilder;
use Drupal\nnphi_curriculum\CurriculumProgress;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnrollmentController extends ControllerBase {

  protected $progressService;

  protected $linkBuilder;

  public function __construct(CurriculumProgress $progressService, CurriculumLinkBuilder $linkBuilder) {
    $this->progressService = $progressService;
    $this->linkBuilder = $linkBuilder;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nnphi_curriculum.progress'),
      $container->get('nnphi_curriculum.link_builder')
    );
  }

  public function curriculumEnroll(NodeInterface $node) {
    if ($node->getType() !== 'curriculum') {
      throw new NotFoundHttpException();
    }
    $response = new AjaxResponse();
    try {
      // Do the enrollment.
      $this->progressService->enrollUserInCurriculum($this->currentUser(), $node);
    }
    catch (\Exception $exception) {
      watchdog_exception('nnphi_curriculum', $exception);
      $this->getLogger('nnphi_curriculum')->error('Error enrolling user @uid in curriculum @nid',
        ['@uid' => $this->currentUser()->id(), '@nid' => $node->id()]);
      $alert = new AlertCommand($this->t('An error occurred. Please try again later.'));
      $response->addCommand($alert);
      return $response;
    }
    // Send back the new link.
    $replace = new ReplaceCommand('div.curriculum-enroll', $this->linkBuilder->curriculum($node->id()));
    $response->addCommand($replace);
    return $response;
  }

  public function completeTraining(NodeInterface $curriculum, NodeInterface $training) {
    if ($curriculum->getType() !== 'curriculum' || $training->getType() !== 'training') {
      throw new NotFoundHttpException();
    }
    $response = new AjaxResponse();
    try {
      $this->progressService->trackProgress($this->currentUser(), $curriculum, $training);
    }
    catch (\Exception $exception) {
      watchdog_exception('nnphi_curriculum', $exception);
      $this->getLogger('nnphi_curriculum')->error('Error enrolling user @uid in training @tid for curriculum @cid',
        ['@uid' => $this->currentUser()->id(), '@tid' => $training->id(), '@cid' => $curriculum->id()]);
      $alert = new AlertCommand($this->t('An error occurred. Please try again later.'));
      $response->addCommand($alert);
    }
    $selector = '.training-completion[data-training="' . $training->id() . '"][data-curriculum="' . $curriculum->id() . '"]';
    $replace = new ReplaceCommand($selector, $this->linkBuilder->training($curriculum->id(), $training->id()));
    $response->addCommand($replace);
    return $response;
  }

}