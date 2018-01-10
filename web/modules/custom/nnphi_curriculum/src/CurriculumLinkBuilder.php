<?php

namespace Drupal\nnphi_curriculum;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

class CurriculumLinkBuilder {

  use StringTranslationTrait;

  /**
   * @var \Drupal\nnphi_curriculum\CurriculumProgress
   */
  protected $curriculumService;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeMnager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  public function __construct(CurriculumProgress $curriculum_service, EntityTypeManagerInterface $entityTypeManager,
                              AccountProxyInterface $currentUser, DateFormatterInterface $dateFormatter) {
    $this->curriculumService = $curriculum_service;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * Link builder to enroll in a curriculum.
   *
   * @param $curriculum_nid
   *
   * @return array
   */
  public function curriculum($curriculum_nid) {
    if (!$this->currentUser->hasPermission('enroll in curricula')) {
      return [];
    }
    /** @var NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($curriculum_nid);
    // Check if the user is enrolled in the curriculum.
    $enrolled = $this->curriculumService->userIsEnrolled($this->currentUser, $node);
    if ($enrolled) {
      return $this->enrolledText();
    }
    $render = [
      '#theme' => 'curriculum_enroll',
      '#action' => 'enroll',
      '#attributes' => [
        'class' => ['use-ajax'],
        'title' => $this->t('Enroll'),
      ],
      '#title' => $this->t('Enroll'),
      '#curriculum_nid' => $curriculum_nid,
    ];
    $url = Url::fromRoute('nnphi_curriculum.enroll.curriculum', ['node' => $node->id()]);
    $rendered_url = $url->toString(TRUE);
    $rendered_url->applyTo($render);
    $render['#attributes']['href'] = $rendered_url->getGeneratedUrl();

    CacheableMetadata::createFromObject($this->currentUser)
      ->applyTo($render);

    return $render;
  }

  /**
   * Link builder for a training enrollment link.
   *
   * @param $curriculum_nid
   * @param $training_nid
   *
   * @return array
   */
  public function training($curriculum_nid, $training_nid) {
    if (!$this->currentUser->hasPermission('enroll in curricula')) {
      return [];
    }
    $ns = $this->entityTypeManager->getStorage('node');
    $curriculum = $ns->load($curriculum_nid);
    // Don't let the user complete a course if they're not enrolled in the curriculum.
    $enrolled = $this->curriculumService->userIsEnrolled($this->currentUser, $curriculum);
    if (!$enrolled) {
      $title = $this->t('Enroll and mark completed');
    }
    else {
      $title = $this->t('Mark as completed');
    }
    $training = $ns->load($training_nid);
    $completed = $this->curriculumService->userHasCompletedCourse($this->currentUser, $curriculum, $training);
    if ($completed) {
      $render = $this->completedText($completed);
    }
    else {
      $content_attributes = new Attribute();
      $content_attributes['class'] = ['training-completion'];
      $content_attributes['data-curriculum'] = $curriculum_nid;
      $content_attributes['data-training'] = $training_nid;

      $render = [
        '#theme' => 'curriculum_training_complete',
        '#action' => 'enroll',
        '#attributes' => [
          'class' => ['use-ajax'],
          'title' => $title,
        ],
        '#container_attributes' => $content_attributes,
        '#title' => $title,
      ];
      $url = Url::fromRoute('nnphi_curriculum.enroll.training', ['curriculum' => $curriculum_nid, 'training' => $training_nid]);
      $rendered_url = $url->toString(TRUE);
      $rendered_url->applyTo($render);
      $render['#attributes']['href'] = $rendered_url->getGeneratedUrl();
    }

    CacheableMetadata::createFromRenderArray($render)
      ->applyTo($render);

    $render['#cache']['contexts'][] = 'user.permissions';

    return $render;
  }

  /**
   * Text to display instead of a link
   * @return array
   */
  protected function enrolledText() {
    return [
      '#markup' => '<div class="curriculum-enrolled">' . $this->t('You are enrolled in this curriculum.') . '</div>',
    ];
  }

  protected function completedText($timestamp) {
    return [
      '#markup' => '<div class="training-completed">' . $this->t('You completed this training @date', ['@date' => $this->dateFormatter->format($timestamp)]) . '</div>',
    ];
  }

}
