<?php

namespace Drupal\nnphi_curriculum;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Class CurriculumProgress.
 */
class CurriculumProgress {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * @var TimeInterface
   */
  protected $time;

  const TRAININGS_FIELD = 'field_curriculum_trainings';

  /**
   * Constructs a new CurriculumProgress object.
   */
  public function __construct(Connection $database, TimeInterface $time) {
    $this->database = $database;
    $this->time = $time;
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   * @param \Drupal\node\NodeInterface $curriculumNode
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   */
  public function enrollUserInCurriculum(AccountProxyInterface $account, NodeInterface $curriculumNode) {
    return $this->database->merge('nnphi_curriculum_enrollment')
      ->keys(['uid' => $account->id(), 'curriculum_nid' => $curriculumNode->id()])
      ->insertFields(
        ['created' => $this->time->getRequestTime(),
        'uid' => $account->id(),
        'curriculum_nid' => $curriculumNode->id()]
      )
      ->execute();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   * @param \Drupal\node\NodeInterface $curriculumNode
   * @param \Drupal\node\NodeInterface $trainingNode
   *
   * @return \Drupal\Core\Database\Query\Merge
   */
  public function trackProgress(AccountProxyInterface $account, NodeInterface $curriculumNode, NodeInterface $trainingNode) {
    // Enroll the user if they aren't already enrolled.
    if (!$this->userIsEnrolled($account, $curriculumNode)) {
      $this->enrollUserInCurriculum($account, $curriculumNode);
    }
    return $this->database->merge('nnphi_curriculum_progress')
      ->keys(['uid' => $account->id(), 'curriculum_nid' => $curriculumNode->id(), 'training_nid' => $trainingNode->id()])
      ->insertFields([
        'completed' => $this->time->getRequestTime(),
        'uid' => $account->id(),
        'curriculum_nid' => $curriculumNode->id(),
        'training_nid' => $trainingNode->id(),
      ])->execute();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *
   * @return array
   */
  public function getUserEnrollments(AccountProxyInterface $account) {
    return $this->database->query("SELECT curriculum_nid FROM {nnphi_curriculum_enrollment}
                                          WHERE uid = :uid", [':uid' => $account->id()])->fetchCol();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   * @param \Drupal\node\NodeInterface $curriculumNode
   *
   * @return bool
   */
  public function userIsEnrolled(AccountProxyInterface $account, NodeInterface $curriculumNode) {
    return (bool)$this->database->query("SELECT 1 FROM {nnphi_curriculum_enrollment} 
                                                WHERE uid = :uid AND curriculum_nid = :cid", [':uid' => $account->id(), ':cid' => $curriculumNode->id()])->fetchField();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   * @param \Drupal\node\NodeInterface $curriculumNode
   * @param \Drupal\node\NodeInterface $trainingNode
   *
   * @return int|bool
   *  The timestamp the user completed the course|false
   */
  public function userHasCompletedCourse(AccountProxyInterface $account, NodeInterface $curriculumNode, NodeInterface $trainingNode) {
    return $this->database->query("SELECT completed FROM {nnphi_curriculum_progress}
                                                WHERE uid = :uid 
                                                AND curriculum_nid = :cid
                                                AND training_nid = :tid ",
                                                [':uid' => $account->id(), ':cid' => $curriculumNode->id(), ':tid' => $trainingNode->id()])->fetchField();
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   * @param \Drupal\node\NodeInterface $curriculumNode
   *
   * @return int
   */
  public function getUserCurriculumProgress(AccountProxyInterface $account, NodeInterface $curriculumNode) {
    // Get the count of trainings in the curriculum.
    $count = $curriculumNode->get(self::TRAININGS_FIELD)->count();
    if (!$count || $count === 0) {
      return 0;
    }
    // Query for progress.
    $completed = $this->database->query("SELECT count(id) FROM {nnphi_curriculum_progress}
                                        WHERE uid = :uid AND curriculum_nid = :cid", [':uid' => $account->id(), ':cid' => $curriculumNode->id()])->fetchField();
    return $completed;
  }

}
