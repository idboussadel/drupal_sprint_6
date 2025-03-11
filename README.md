# Sprint 6 - Drupal 

## Table of Contents

- [Day 1: Work with Data](#day-1-work-with-data-entity--field-api-configuration-entities)
- [Day 2: Work with Hooks](#day-2-work-with-hooks)

---  

### Day 1: Work with Data (Entity & Field API, Configuration Entities)

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

you can also have custom validation :

```php
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

```

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/5e744eca-f9eb-49b9-8499-36ecfacb0d59" />


---

2. **What is AccessResult and how does it work ?**

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

3. **Creating a custom entity involves a lot of moving parts and boilerplate code, how do you quickly generate and scafold a new entity codebase ?**

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
./vendor/bin/drush config:get mymodule.settings
```

<img width="1102" alt="image" src="https://github.com/user-attachments/assets/3ff48d18-6f38-43ad-a943-0473cff0e783" />

Using a custom module :
( 🆘 if it doesnt work make sure to uninstall and install module again to config to take place )

`config/install/content_entity_example.settings.yml` :

```yaml
example_setting: 'Hello content entity example'
enabled: true
notification_email: 'a.idboussadel@void.fr'
```

`config/schema/content_entity_example.schema.yml`:

```yaml
content_entity_example.settings:
  type: config_object
  label: 'Content Entity Example Settings'
  mapping:
    example_setting:
      type: string
      label: 'Example Setting'
    enabled:
      type: boolean
      label: 'Enable Feature'
    notification_email:
      type: string
      label: 'Notification Email'
```

<img width="879" alt="image" src="https://github.com/user-attachments/assets/f098441f-16c2-4956-9213-69c3fc35fec3" />

---

### Day 2: Work with Hooks

1. **How do you add a new field base to an existing entity type using hook_entity_base_field_info?**

To add a new base field to an existing entity type, implement hook_entity_base_field_info() in your custom module.

```php
/**
 * Implements hook_entity_base_field_info().
 */
function MYMODULE_entity_base_field_info(\Drupal\Core\Entity\EntityTypeInterface $entity_type) {
  $fields = [];

  if ($entity_type->id() === 'node') {
    $fields['my_custom_field'] = \Drupal\Core\Field\BaseFieldDefinition::create('string')
      ->setLabel(t('My Custom Field'))
      ->setDescription(t('A custom base field for nodes.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  return $fields;
}
```

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/aa883651-cc0b-44c3-84fe-c4c231b7d88c" />


---

2. **What is the role of hook_update_n?**

Suppose you have a custom module called my_module, and in version 1.0, you didn't have a field for "subtitle" on your article content type. In version 1.1, you decide to add a "subtitle" field. You need to ensure that all existing sites using your module get this new field when they update.
🆘 hook_update_N() is defined in your module's `.install` file. 

```php
<?php

/**
 * Implements hook_update_N().
 * Add a new "subtitle" field to the article content type.
 */
function content_entity_example_update_8001()
{
    // Create the field storage definition.
    $field_storage = \Drupal\field\Entity\FieldStorageConfig::create([
        'field_name' => 'field_subtitle',
        'entity_type' => 'node',
        'type' => 'string',
        'settings' => [],
    ]);
    $field_storage->save();

    // Add the field to the "article" content type.
    $field_config = \Drupal\field\Entity\FieldConfig::create([
        'field_name' => 'field_subtitle',
        'entity_type' => 'node',
        'bundle' => 'article',
        'label' => 'Subtitle',
    ]);
    $field_config->save();
}

```

<img width="995" alt="image" src="https://github.com/user-attachments/assets/6c71e8bf-52a1-4b4d-9e6b-55bc2e3115b5" />

<img width="1438" alt="image" src="https://github.com/user-attachments/assets/e47a3c63-b51a-445a-a776-26408ca43264" />

---

3. **What is the role of hook_install?**

`hook_install` is used to perform actions when a module is installed. This is where you can set up default configurations, create database tables, or initialize module-specific settings.

```php
<?php

/**
 * Implements hook_install().
 */
function MYMODULE_install() {
  // Create a default role.
  $role = \Drupal\user\Entity\Role::create([
    'id' => 'custom_role',
    'label' => 'Custom Role',
  ]);
  $role->save();
}
```

---

4. **How would you prefix all your newly created nodes (type: article) with HEY- using hook_ENTITY_TYPE_presave?**

To prefix all newly created nodes of type "article" with "HEY-", implement `hook_ENTITY_TYPE_presave` for nodes.

```php
<?php

/**
 * Implements hook_ENTITY_TYPE_presave().
 */
function MYMODULE_node_presave(\Drupal\Core\Entity\EntityInterface $entity) {
  if ($entity->bundle() === 'article' && $entity->isNew()) {
    $title = $entity->getTitle();
    $entity->setTitle('HEY-' . $title);
  }
}
```

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/3974bcbc-5f8b-45a5-b61c-336f60a57d7f" />

---

5. **What is the role of $entity->original?**

`$entity->original` holds the original version of the entity before it was modified. This is useful for comparing changes between the original and the updated entity.

```php
<?php

function MYMODULE_node_presave(\Drupal\Core\Entity\EntityInterface $entity) {
  if ($entity->bundle() === 'article') {
    $original_title = $entity->original->getTitle();
    $new_title = $entity->getTitle();
    if ($original_title !== $new_title) {
      // Do something if the title has changed.
    }
  }
}
```

---

6. **How can you override a Theme Hook provided by another module?**

To override a theme hook provided by another module, you can implement `hook_theme_suggestions_HOOK_alter` to add your own theme suggestions.

```php
<?php

/**
 * Implements hook_theme_suggestions_HOOK_alter() for node templates.
 */
function content_entity_example_theme_suggestions_node_alter(array &$suggestions, array $variables)
{

    if (isset($variables['elements']['#node']) && $variables['elements']['#node'] instanceof \Drupal\node\NodeInterface) {
        if ($variables['elements']['#node']->bundle() == 'article') {
            $suggestions[] = 'node__article__custom';
        }
    }
}
```

```twig
{#
/**
 * @file
 * Custom template for article nodes.
 */
#}
{{ dump() }}

<article>
  <p>My custom node artical ( override ) template</p>
  <div>{{ content }}</div>
</article>
```

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/0fe588b6-afb3-41c7-90b2-6bf7bca1b533" />


---

7. **Using hook_theme_suggestions_alter how can you add a new theme suggestion for $hook === 'user' based on the view mode?**

To add a new theme suggestion for the 'user' hook based on the view mode, implement `hook_theme_suggestions_alter`.

```php
<?php

/**
 * Implements hook_theme_suggestions_alter().
 */
function mymodule_theme_suggestions_alter(array &$suggestions, array $variables, $hook) {
  if ($hook === 'user' && isset($variables['elements']['#view_mode'])) {
    $view_mode = $variables['elements']['#view_mode'];
    $suggestions[] = 'user__' . $view_mode;
  }
}
```

- `user--full.html.twig`
```twig
{#
/**
 * @file
 * Custom template for user full view mode.
 */
#}
<div class="user-full">
  <p>This is the custom user full template.</p>
  <div>{{ content }}</div>
</div>
```

- `user--teaser.html.twig`
```twig
{#
/**
 * @file
 * Custom template for user teaser view mode.
 */
#}
<div class="user-teaser">
  <p>This is the custom user teaser template.</p>
</div>
```

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/29058e2c-e9b0-4eb5-ae73-20006d1f2ce3" />
