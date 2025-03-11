<?php

namespace Drupal\password_constraint\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Defines the Password Policy Constraint.
 *
 * @Constraint(
 *   id = "PasswordPolicyConstraint",
 *   label = @Translation("Password Policy Constraint", context = "Validation")
 * )
 */
class PasswordPolicyConstraint extends Constraint
{
    public $message = "The password does not meet the following requirements:";
    public $listOpen = "<ul>";
    public $listClose = "</ul>";
    public $itemFormat = "<li>%item</li>";
}
