<?php

namespace Drupal\mymodule\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Constraint(
 *   id = "PasswordPolicyConstraint",
 *   label = @Translation("Password Policy Constraint", context = "Validation"),
 * )
 */
class PasswordPolicyConstraint extends Constraint
{
    public $message = 'The password does not meet the policy requirements.';
}
