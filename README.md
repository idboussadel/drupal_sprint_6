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

---

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

---

3. ** Creating a custom entity involves a lot of moving parts and boilerplate code, how do you quickly generate and scafold a new entity codebase ?**

Using Drush:
Install Drush if not already installed:

```bash
composer require drush/drush
```

Use the drush generate command:

```bash
./vendor/bin/drush generate entity:content
```

<img width="1024" alt="image" src="https://github.com/user-attachments/assets/2377b0ca-25d0-4560-98c1-842ea697d0ef" />

<img width="535" alt="image" src="https://github.com/user-attachments/assets/8f62d000-d634-4f00-bc51-fb1b5733e5d6" />

---

4. **How to Get a Field Definition Using Code?**

To retrieve a field definition in Drupal programmatically, you can use the following code:

```php
<?php
$field_definition = \Drupal::entityTypeManager()
  ->getStorage('field_storage_config')
  ->load('node.field_body');
```

---

5. **Is it Possible to Create Multiple Field Formatters for a Field Type? If So, How?**

In Drupal, field formatters are plugins that define how field values are displayed to the end user. When you create a field on an entity (e.g., a node, user, taxonomy term), you can choose from a set of formatters to control how the field's content is rendered on the front-end.

Yes, it is possible to create multiple field formatters for a field type.

To create multiple field formatters, you need to:

- Define a New Formatter Plugin: Implement a new plugin class in your module that extends the FieldFormatterBase class.
- Alter the Formatter Definition: Define the settings for the formatter, such as options or configuration.

```php
<?php
namespace Drupal\mymodule\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field\Plugin\Field\FieldFormatter\FieldFormatterBase;
use Drupal\field\FieldStorageDefinitionInterface;

/**
 * Plugin to define a custom formatter for a text field.
 *
 * @FieldFormatter(
 *   id = "custom_text_formatter",
 *   label = @Translation("Custom Text Formatter"),
 *   field_types = {"text"},
 * )
 */
class CustomTextFormatter extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => '<div class="custom-text">' . $item->value . '</div>',
      ];
    }
    return $elements;
  }
}
```

---

6. **Using drush, how to retrieve a module configuration (settings) ?**

To retrieve a module's configuration using Drush, use the config:get command:

```bash
drush config:get mymodule.settings
```

Replace mymodule.settings with the configuration name for your module. If you don’t know the configuration name, you can list all configurations:

```bash
drush config:list
```

To filter configurations for a specific module:

```bash
drush config:list | grep mymodule
```