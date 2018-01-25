<?php

namespace Drupal\nnphi_training;

use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

class ReviewLazyBuilders {

  /**
   * @var \Drupal\comment\CommentStorageInterface
   */
  protected $commentStorage;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              EntityFormBuilderInterface $entityFormBuilder, AccountProxyInterface $account) {
    $this->commentStorage = $entityTypeManager->getStorage('comment');
    $this->account = $account;
    $this->entityFormBuilder = $entityFormBuilder;
  }

  /**
   * #lazy_builder callback for the review form.
   *
   * @param $commented_entity_type_id
   * @param $commented_entity_id
   * @param $field_name
   * @param $comment_type_id
   *
   * @return array
   * @see \Drupal\comment\CommentLazyBuilders::renderForm()
   */
  public function renderForm($commented_entity_type_id, $commented_entity_id, $field_name, $comment_type_id) {
    // If the user already has a review for this training, show the edit form.
    $exists = $this->commentStorage->loadByProperties([
      'comment_type' => 'training_review',
      'entity_id' => $commented_entity_id,
      'uid' => $this->account->id()
    ]);
    if ($exists) {
      $comment = end($exists);
    }
    else {
      $values = [
        'entity_type' => $commented_entity_type_id,
        'entity_id' => $commented_entity_id,
        'field_name' => $field_name,
        'comment_type' => $comment_type_id,
        'pid' => NULL,
      ];
      $comment = $this->commentStorage->create($values);
    }
    return $this->entityFormBuilder->getForm($comment);
  }
}
