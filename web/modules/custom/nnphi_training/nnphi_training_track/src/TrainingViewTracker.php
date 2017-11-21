<?php

namespace Drupal\nnphi_training_track;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\redis\ClientFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TrainingViewTracker
 *
 * @package Drupal\nnphi_training_track
 */
class TrainingViewTracker {

  /**
   * @var \Drupal\redis\ClientFactory
   */
  private $clientFactory;

  /**
   * @var TimeInterface
   */
  private $time;

  /**
   * @var \Redis
   */
  private $client;

  public function __construct(RequestStack $requestStack, ClientFactory $clientFactory, TimeInterface $time) {
    $this->clientFactory = $clientFactory;
    $this->time = $time;
    $this->client = $clientFactory::getClient();
  }

  /**
   * @param int $nid
   * @param int $uid
   *
   * @return int Success or failure
   */
  public function recordView($nid, $uid) {
    $key = $this->getRedisKey($uid);
    // Limit the amount of data being stored.
    // Delete any records older than one week.
    $oldest = strtotime('-1 month');
    $this->client->zRemRangeByScore($key, 0, $oldest);
    // Write the view as a record in a Redis sorted set.
    // If the user has already viewed the node, the timestamp "score" will be updated.
    return $this->client->zAdd($key, $this->time->getRequestTime(), $nid);
  }

  /**
   * Get a list of training NIDs viewed by a user.
   *
   * @param int $uid
   * @param int $limit
   *
   * @return array
   */
  public function getViewsForUser($uid, $limit = 10) {
    $key = $this->getRedisKey($uid);
    // Retrieve the data, ordered by "score" (timestamp) descending.
    $viewed = $this->client->zRevRange($key, 0, $limit, TRUE);
    return $viewed;
  }

  /**
   * Delete a user's data from storage.
   *
   * @param int $uid
   */
  public function deleteUserData($uid) {
    $key = $this->getRedisKey($uid);
    $this->client->delete($key);
  }

  /**
   * Get the redis key used to store data.
   *
   * @param $uid
   *
   * @return string
   */
  private function getRedisKey($uid) {
    return "user:$uid:viewed";
  }
}
