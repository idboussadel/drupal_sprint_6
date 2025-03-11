<?php

namespace Drupal\mymodule\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PasswordPolicyConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface
{

    protected $passwordPolicyValidator;

    public function __construct($passwordPolicyValidator)
    {
        $this->passwordPolicyValidator = $passwordPolicyValidator;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('password_policy.validator')
        );
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$this->passwordPolicyValidator->validatePassword($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
