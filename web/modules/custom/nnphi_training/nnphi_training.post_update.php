<?php
/**
 * @file
 *  Post-update hooks for nnphi_training module.
 */

/**
 * Implements hook_post_update_NAME().
 *
 * Move old expiration field data into new expiration field.
 */
function nnphi_training_post_update_expiration_date(&$sandbox) {
  /** @var \Drupal\node\NodeInterface[] $nodes */
  $nodes = \Drupal::entityTypeManager()->getStorage('node')
    ->loadByProperties(['type' => 'training']);
  foreach ($nodes as $node) {
    // Transfer the old expiration field to the new one.
    $date = $node->field_training_expiration->date;
    if (!$date) {
      continue;
    }
    $timestamp = $date->getTimestamp();
    $date = \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp($timestamp);
    $node->set('field_training_expiration_date', $date->format('Y-m-d'));
    $node->save();
  }
}

/**
 * Set the published date for existing training nodes as the node created.
 */
function nnphi_training_post_update_published_date(&$sandbox) {
  /** @var \Drupal\node\NodeInterface[] $nodes */
  $nodes = \Drupal::entityTypeManager()->getStorage('node')
    ->loadByProperties(['type' => 'training']);
  foreach ($nodes as $node) {
    // Transfer the node->created into the new field.
    if (!$node->hasField('field_training_published_date')) {
      continue;
    }
    $timestamp = $node->getCreatedTime();
    $date = \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp($timestamp);
    $node->set('field_training_published_date', $date->format('Y-m-d'));
    $node->save();
  }
}
