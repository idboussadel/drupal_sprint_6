<?php

namespace Drupal\content_entity_example\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the content_entity_example entity edit forms.
 *
 * @ingroup content_entity_example
 */
class ContactForm extends ContentEntityForm
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        /* @var $entity \Drupal\content_entity_example\Entity\Contact */
        $form = parent::buildForm($form, $form_state);
        $entity = $this->entity;

        $form['langcode'] = [
            '#title' => $this->t('Language'),
            '#type' => 'language_select',
            '#default_value' => $entity->getUntranslated()->language()->getId(),
            '#languages' => Language::STATE_ALL,
        ];
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        $form_state->setRedirect('entity.content_entity_example_contact.collection');
        $entity = $this->getEntity();
        $result =  $entity->save();

        $message_arguments = ['%label' => $this->entity->label()];

        if ($result == SAVED_NEW) {
            $this->messenger()->addStatus($this->t('New contact %label has been created.', $message_arguments));
        } else {
            $this->messenger()->addStatus($this->t('The contact %label has been updated.', $message_arguments));
        }
    }
}
