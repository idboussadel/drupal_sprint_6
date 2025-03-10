# Sprint 6 - Drupal 

## Day 1: Work with Data (Entity & Field API, Configuration Entities)

1. **Using hook_entity_base_field_info_alter alter user entity type and add a constraint to the pass field**

we create the `src/Plugin/Validation/Constraint/PasswordPolicyConstraint.php`

```php
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
```

then :

```php
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
```

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/a7726ee5-c044-488d-8d15-1e304e26f732" />


<img width="1440" alt="image" src="https://github.com/user-attachments/assets/1bc5fcb3-d3d4-4fe2-88f2-56cab0d81bbb" />

2. **What is AccessResult and how does it work ?**

2. What is AccessResult and How Does It Work?
AccessResult is a class in Drupal that represents the outcome of a permission check. It is used to handle access control in Drupal. When you check if a user can access a resource (like a page, content, or an action), you return an AccessResult object.

-  `AccessResult::allowed()` : Indicates that access is granted.
-  `AccessResult::forbidden()` : Indicates that access is denied.
-  `AccessResult::neutral()` : Indicates that access is undecided and depends on other factors.

```php
<?php
use Drupal\Core\Access\AccessResult;

/**
 * Check if the user has permission to view a content type.
 */
function mymodule_access_check($account) {
  if ($account->hasPermission('view content')) {
    return AccessResult::allowed();
  }
  return AccessResult::forbidden();
}
```