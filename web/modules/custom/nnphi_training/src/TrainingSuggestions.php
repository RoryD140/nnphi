<?php

namespace Drupal\nnphi_training;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class TrainingSuggestions {

  private $entityTypeManager;

  private $userStorage;

  private $nodeStorage;

  private $db;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, Connection $connection) {
    $this->entityTypeManager = $entityTypeManager;
    $this->db = $connection;
  }

  /**
   * Get a list of suggested nodes for an account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return array|NodeInterface
   */
  public function getSuggestionsForUser(AccountInterface $account) {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->userStorage()->load($account->id());
    // Extract interests from the user.
    $interests = $account->get('field_user_interests');
    if ($interests->isEmpty()) {
      return [];
    }
    $terms = $interests->getValue();
    $tids = array_column($terms, 'target_id');
    $nids = $this->suggestedTrainingsQuery($tids, $account->id());
    if (count($nids) < 10) {
      $flagged_nids = $this->userFlaggings($account->id());
      if ($flagged_nids) {
        $nids += $this->relatedFlaggings($flagged_nids, $account->id());
      }
    }
    if (empty($nids)) {
      return [];
    }
    $nids = $this->reduceByFlaggings($nids, $account->id());
    if (empty($nids)) {
      return [];
    }
    $nids = $this->reduceByRecent($nids, $account->id());
    if (!empty($nids)) {
      return $this->nodeStorage()->loadMultiple($nids);
    }
    return [];
  }

  /**
   * @param array $tids
   * @param $uid
   *
   * @return mixed
   */
  protected function suggestedTrainingsQuery(array $tids) {
    return $this->db->query("SELECT ti.nid, count(ti.nid) AS count FROM {taxonomy_index} ti
                                        INNER JOIN {node_field_data} n ON n.nid = ti.nid
                                        WHERE ti.tid IN (:tids[])
                                        AND n.type = 'training'
                                        AND n.status = :np
                                        GROUP BY ti.nid
                                        ORDER BY count DESC, n.created DESC
                                        LIMIT 0, 10;
                                ", [':tids[]' => $tids, ':np' => NodeInterface::PUBLISHED])->fetchCol();
  }

  /**
   * Reduce suggested NIDs by a users flaggings.
   *
   * @param array $nids
   * @param $uid
   *
   * @return array
   */
  protected function reduceByFlaggings(array $nids, $uid) {
    // Find differences between flaggings and the NIDs found in the query.
    $bookmarks = $this->db->query("SELECT entity_id FROM {flagging}
                                    WHERE entity_type = 'node'
                                    AND flag_id = 'bookmark'
                                    AND entity_id IN (:nids[])
                                    AND uid = :uid;
                                    ", [':nids[]' => $nids, ':uid' => $uid])->fetchCol();
    if (!$bookmarks) {
      return $nids;
    }
    return array_diff($nids, $bookmarks);
  }

  /**
   * Get "users who bookmarked this also bookmarked" for trainings.
   *
   * @param array $nids
   * @param $uid
   *  UID of flaggings to exclude.
   *
   * @return array
   */
  protected function relatedFlaggings(array $nids, $uid) {
    $related_flags = $this->db->query("SELECT L2.entity_id, COUNT(L2.uid) as num_users from {flagging} L1
                                              INNER JOIN {node_field_data} n ON n.nid = L1.entity_id
                                              JOIN {flagging} L2
                                              WHERE L1.entity_id IN (:nids[]) and L1.entity_type='node'
                                              AND L2.uid = L1.uid
                                              AND L2.uid != :uid
                                              AND n.type = 'training'
                                              AND n.status = :np
                                              GROUP BY L2.entity_id
                                              ORDER BY num_users, n.created DESC", [':nids[]' => $nids, ':uid' => $uid, ':np' => NodeInterface::PUBLISHED])->fetchCol();
    if (!$related_flags) {
      return $nids;
    }
    return $related_flags;
  }

  /**
   * Return a list of nodes the user has flagged
   * with the "bookmark" flag.
   *
   * @param $uid
   *
   * @return mixed
   */
  protected function userFlaggings($uid) {
    return $this->db->query("SELECT entity_id FROM {flagging}
                                    WHERE entity_type = 'node'
                                    AND uid = :uid
                                    AND flag_id = 'bookmark'
                                    ORDER BY created DESC
                                    LIMIT 0, 10", [':uid' => $uid])->fetchCol();
  }

  /**
   * Reduce a list of NIDs by the nodes a user has recently viewed.
   * 
   * @param $nids
   * @param $uid
   *
   * @return array
   */
  protected function reduceByRecent($nids, $uid) {
    // Find differences between recently viewed nodes and the NIDs found in the query.
    $recent = $this->db->query("SELECT nid FROM {history}
                                    WHERE nid IN (:nids[])
                                    AND uid = :uid;
                                    ", [':nids[]' => $nids, ':uid' => $uid])->fetchCol();
    if (!$recent) {
      return $nids;
    }
    return array_diff($nids, $recent);
  }

  /**
   * @return \Drupal\user\UserStorageInterface
   */
  private function userStorage() {
    if (!isset($this->userStorage)) {
      $this->userStorage = $this->entityTypeManager->getStorage('user');
    }
    return $this->userStorage;
  }

  /**
   * @return \Drupal\taxonomy\TermStorageInterface
   */
  private function termStorage() {
    if (!isset($this->termStorage)) {
      $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    }
    return $this->termStorage;
  }

  /**
   * @return \Drupal\node\NodeStorageInterface
   */
  private function nodeStorage() {
    if (!isset($this->nodeStorage)) {
      $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    }
    return $this->nodeStorage;
  }
}
