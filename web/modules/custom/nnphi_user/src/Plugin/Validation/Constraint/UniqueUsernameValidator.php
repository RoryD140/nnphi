<?php

namespace Drupal\nnphi_user\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueUsernameValidator extends ConstraintValidator {

  public function validate($item, Constraint $constraint) {
    $uid = $item->getEntity()->id();
    $name = $item->getString();
    // Check if there's another user with this name.
    $exists = (bool)\Drupal::entityQuery('user')
      ->condition('field_user_username', $name)
      ->condition('uid', $uid, '!=')
      ->range(0, 1)
      ->count()
      ->execute();
    if ($exists) {
      $this->context->addViolation(t('%value is already taken.', ['%value' => $name]));
    }
  }

}
