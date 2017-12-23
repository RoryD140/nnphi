<?php

namespace Drupal\nnphi_curriculum;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

class CurriculumLinkBuilder {

  /**
   * @var \Drupal\nnphi_curriculum\CurriculumProgress
   */
  protected $curriculumService;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeMnager;

  public function __construct(CurriculumProgress $curriculum_service, EntityTypeManagerInterface $entityTypeManager) {
    $this->curriculumService = $curriculum_service;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function curriculum($curriculum_nid) {
    /** @var NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($curriculum_nid);
    $url = Url::fromRoute('nnphi_curriculum.enroll.curriculum', ['node' => $node->id()],
      ['attributes' => ['class' => ['use-ajax']]]);
    $link = [];
    $url->toString(TRUE)->applyTo($link);
    return $link;
  }
}