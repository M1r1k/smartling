<?php

namespace Drupal\smartling;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SmartlingEntityHandler implements EntityHandlerInterface {

  /**
   * The type of the entity being translated.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * Information about the entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The content translation manager.
   *
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  protected $manager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  protected

  /**
   * Initializes an instance of the content translation controller.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The info array of the given entity type.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\content_translation\ContentTranslationManagerInterface $manager
   *   The content translation manager service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(EntityTypeInterface $entity_type, LanguageManagerInterface $language_manager, ContentTranslationManagerInterface $manager, EntityManagerInterface $entity_manager, AccountInterface $current_user) {
    $this->entityTypeId = $entity_type->id();
    $this->entityType = $entity_type;
    $this->languageManager = $language_manager;
    $this->manager = $manager;
    $this->currentUser = $current_user;
  }

  /**
   * Instantiates a new instance of this entity handler.
   *
   * This is a factory method that returns a new instance of this object. The
   * factory should pass any needed dependencies into the constructor of this
   * object, but not the container itself. Every call to this method must return
   * a new instance of this object; that is, it may not implement a singleton.
   *
   * @param ContainerInterface $container
   *   The service container this object should use.
   * @param EntityTypeInterface $entity_type
   *   The entity type definition.
   *
   * @return static
   *   A new instance of the entity handler.
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('language_manager'),
      $container->get('content_translation.manager'),
      $container->get('entity.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Performs the needed alterations to the entity form.
   *
   * @param array $form_object
   *   The entity form to be altered to provide the translation workflow.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being created or edited.
   * @todo move docs and description to interface.
   */
  public function entityFormAlter(array &$form, FormStateInterface $form_state, EntityInterface $entity) {
    // @see ContentTranslationHandler:entityFormAlter()
    $form_object = $form_state->getFormObject();
    $form_langcode = $form_object->getFormLangcode($form_state);
    $entity_langcode = $entity->getUntranslated()->language()->getId();

    $new_translation = !empty($source_langcode);
    $translations = $entity->getTranslationLanguages();
    if ($new_translation) {
      // Make sure a new translation does not appear as existing yet.
      unset($translations[$form_langcode]);
    }
    $is_translation = !$form_object->isDefaultFormLangcode($form_state);
    $has_translations = count($translations) > 1;
    if ($new_translation || $has_translations) {
      $form['content_translation']['smartling_target_locales'] = [
        '#title' => t('Target locales'),
        '#type' => 'language_select',
        '#multiple' => TRUE,
      ];

      $form['content_translation']['smartling_send_translation'] = [
        '#value' => t('Send translation to smartling'),
        '#type' => 'submit',
        '#validate' => [[$this, 'validate']]
      ];
    }
  }

  public static function validate(array &$form, FormStateInterface $form_state) {
    $here = 'here';

  }

}
