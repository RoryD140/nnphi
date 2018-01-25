<?php

namespace Drupal\nnphi_training\Controller;

use Drupal\comment\Controller\CommentController as BaseCommentController;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;

class CommentController extends BaseCommentController {
  public function replyFormAccess(EntityInterface $entity, $field_name, $pid = NULL) {
    // Training reviews don't have replies.
    if ($field_name === 'field_training_reviews' && $pid !== NULL) {
      return AccessResult::forbidden();
    }
    return parent::replyFormAccess($entity, $field_name, $pid);
  }
}
