<?php

namespace Drupal\content_entity_example\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\content_entity_example\ContactInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;


/**
 * Defines the ContentEntityExample entity.
 *
 * @ingroup content_entity_example
 *
 *  The following annotation is the actual definition of the entity type which
 *  is read and cached. Don't forget to clear cache after changes.
 *
 * @ContentEntityType(
 *   id = "content_entity_example_contact",
 *   label = @Translation("Contact entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\content_entity_example\Entity\Controller\ContactListBuilder",
 *		 "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\content_entity_example\ContactAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\content_entity_example\Form\ContactForm",
 *       "edit" = "Drupal\content_entity_example\Form\ContactForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "contact",
 *   admin_permission = "administer contact entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "user_id",
 *   },
 *   links = {
 *     "canonical" = "/content_entity_example_contact/{content_entity_example_contact}",
 *     "add-form" = "/content_entity_example_contact/add",
 *     "edit-form" = "/content_entity_example_contact/{content_entity_example_contact}/edit",
 *     "delete-form" = "/contact/{content_entity_example_contact}/delete",
 *     "collection" = "/content_entity_example_contact/list"
 *   },
 *   field_ui_base_route = "content_entity_example.contact_settings",
 * )
 */

class Contact extends ContentEntityBase implements ContactInterface
{
    use EntityChangedTrait;
    use EntityOwnerTrait;
    use StringTranslationTrait;

    /**
     * {@inheritdoc}
     *
     * When a new entity instance is added, set the user_id entity reference to
     * the current user as the creator of the instance.
     */
    public function preSave(EntityStorageInterface $storage)
    {
        parent::preSave($storage);
        if (!$this->getOwnerId()) {
            // If no owner has been set explicitly, make the anonymous user the owner.
            $this->setOwnerId(0);
        }
    }

    /**
     * {@inheritdoc}
     *
     * Define the field properties here.
     *
     * Field name, type and size determine the table structure.
     *
     * In addition, we can define how the field and its content can be manipulated
     * in the GUI. The behaviour of the widgets used can be determined here.
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {

        // In ContentEntityBase, baseFieldDefinitions defines fields
        // id, uuid, revision, langcode, and bundle.
        $fields = parent::baseFieldDefinitions($entity_type);

        // Also add any default base field definitions from EntityOwnerTrait. When using any other
        // entity traits in your code you should check to see if the trait provides any default
        // base fields and make sure you call that method.
        $fields += static::ownerBaseFieldDefinitions($entity_type);

        // If you want to override any definitions of fields otherwise defined
        // in ContentEntityBase::baseFieldDefinitions, do so here.

        // Name field for the contact.
        // We set display options for the view as well as the form.
        // Users with correct privileges can change the view and edit configuration.
        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setDescription(t('The name of the Contact entity.'))
            ->setSettings([
                'max_length' => 255,
                'text_processing' => 0,
            ])
            // Set no default value.
            ->setDefaultValue(NULL)
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => -6,
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => -6,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['first_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('First Name'))
            ->setDescription(t('The first name of the Contact entity.'))
            ->setSettings([
                'max_length' => 255,
                'text_processing' => 0,
            ])
            // Set no default value.
            ->setDefaultValue(NULL)
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => -5,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        // Owner field of the contact.
        // Entity reference field, holds the reference to the user object.
        // The view shows the username field of the user.
        // The form presents an autocomplete field for the username.
        // The key in $fields['user_id'] needs to match whatever is set in
        // the 'owner' entity key in the annotation in order for the
        // EntityOwnerTrait to work as designed.
        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('User Name'))
            ->setDescription(t('The Name of the associated user.'))
            ->setSetting('target_type', 'user')
            ->setSetting('handler', 'default')
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'author',
                'weight' => -3,
            ])
            ->setDisplayOptions('form', [
                'type' => 'entity_reference_autocomplete',
                'settings' => [
                    'match_operator' => 'CONTAINS',
                    'match_limit' => 10,
                    'size' => 60,
                    'placeholder' => '',
                ],
                'weight' => -3,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        // Role field for the contact.
        // The values shown in options are 'administrator' and 'user'.
        $fields['role'] = BaseFieldDefinition::create('list_string')
            ->setLabel(t('Role'))
            ->setDescription(t('The role of the Contact entity.'))
            ->setSettings([
                'allowed_values' => [
                    'administrator' => 'administrator',
                    'user' => 'user',
                ],
            ])
            // Set the default value of this field to 'user'.
            ->setDefaultValue('user')
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => -2,
            ])
            ->setDisplayOptions('form', [
                'type' => 'options_select',
                'weight' => -2,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

        return $fields;
    }
}
