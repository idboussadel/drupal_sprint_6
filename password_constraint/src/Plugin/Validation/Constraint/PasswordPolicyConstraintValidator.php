<?php

namespace Drupal\password_constraint\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Password Policy Constraint.
 */
class PasswordPolicyConstraintValidator extends ConstraintValidator
{
    /**
     * Validates password based on custom policy rules.
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof \Drupal\Core\Field\FieldItemListInterface) {
            $value = $value->value;
        }

        $errors = [];

        if (!preg_match('/[\W_]/', $value)) {
            $errors[] = "Password must contain at least one special character.";
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }

        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = "Password must contain at least one number.";
        }

        if (!empty($errors)) {
            $list_items = '';
            foreach ($errors as $error) {
                $list_items .= str_replace('%item', $error, $constraint->itemFormat);
            }

            $formatted_message = $constraint->message . $constraint->listOpen . $list_items . $constraint->listClose;

            $this->context->buildViolation($formatted_message)
                ->setInvalidValue($value)
                ->addViolation();
        }
    }
}
