<?php

namespace Drupal\nnphi_user\Plugin\Validation\Constraint;

use Drupal\Core\Annotation\Translation;
use Symfony\Component\Validator\Constraint;

/**
 * @Constraint(
 *   id = "unique_username",
 *   label = @Translation("Unique username", context = "Validation"),
 * )
 */

class UniqueUsername extends Constraint {

}