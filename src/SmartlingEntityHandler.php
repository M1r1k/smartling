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
use Drupal\smartling\Entity\SmartlingEntityData;
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

  /**
   * @var \Drupal\smartling\SourceManager
   */
  protected $sourceManager;

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
  public function __construct(EntityTypeInterface $entity_type, LanguageManagerInterface $language_manager, ContentTranslationManagerInterface $manager, EntityManagerInterface $entity_manager, AccountInterface $current_user, SourceManager $source_manager) {
    $this->entityTypeId = $entity_type->id();
    $this->entityType = $entity_type;
    $this->languageManager = $language_manager;
    $this->manager = $manager;
    $this->currentUser = $current_user;
    $this->sourceManager = $source_manager;
  }

  /**
   * Instantiates a new instance of this entity handler.
   *
   * This is a factory method that returns a new instance of this object. The
   * factory should pass any needed dependencies into the constructor of this
   * object, but not the container itself. Every call to this method must return
   * a new instance of this object; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this object should use.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
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
      $container->get('current_user'),
      $container->get('plugin.manager.smartling.source')
    );
  }

  /**
   * Performs the needed alterations to the entity form.
   *
   * @param array $form
   *   The entity form to be altered to provide the translation workflow.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being created or edited.
   *
   * @todo move docs and description to interface.
   */
  public function entityFormAlter(array &$form, FormStateInterface $form_state, EntityInterface $entity) {
    // @see ContentTranslationHandler:entityFormAlter()
    $form_object = $form_state->getFormObject();
    $form_langcode = $form_object->getFormLangcode($form_state);

    $new_translation = !empty($source_langcode);
    $translations = $entity->getTranslationLanguages();
    if ($new_translation) {
      // Make sure a new translation does not appear as existing yet.
      unset($translations[$form_langcode]);
    }
    $has_translations = count($translations) > 1;
    if ($new_translation || $has_translations) {
      $form['content_translation']['smartling_target_locales'] = [
        '#title' => t('Target locales'),
        '#type' => 'language_select',
        '#multiple' => TRUE,
      ];

      $form['content_translation']['smartling_upload_translation'] = [
        '#value' => t('Upload translation'),
        '#type' => 'submit',
        '#validate' => [[$this, 'uploadTranslation']]
      ];

      $form['content_translation']['smartling_download_translation'] = [
        '#value' => t('Download translation'),
        '#type' => 'submit',
        '#validate' => [[$this, 'downloadTranslation']]
      ];
    }
  }

  public function uploadTranslation(array &$form, FormStateInterface $form_state) {
    $form_object = $form_state->getFormObject();
    $entity = $form_object->getEntity();
    $locales = $form_state->getValue('smartling_target_locales');
    /* @var \Drupal\smartling\Plugin\smartling\Source\ContentEntitySource $source_content_plugin */
    $source_content_plugin = $this->sourceManager->getDefinition('content');
    // Get random smartling entity for our entity because we don't care about
    // target language, as we will use only source file name from it.
    $smartling_entity = SmartlingEntityData::loadByConditions(['rid' => $entity->id()]);
    $source_content_plugin->uploadEntity($smartling_entity, $locales);
  }

  public function downloadTranslation(array &$form, FormStateInterface $form_state) {
    $form_object = $form_state->getFormObject();
    $entity = $form_object->getEntity();
    $locales = $form_state->getValue('smartling_target_locales');
    /* @var \Drupal\smartling\Plugin\smartling\Source\ContentEntitySource $source_content_plugin */
    $source_content_plugin = $this->sourceManager->getDefinition('content');
    // Get random smartling entity for our entity because we don't care about
    // target language, as we will use only source file name from it.
    $smartling_entity = SmartlingEntityData::loadByConditions(['rid' => $entity->id()]);
    $source_content_plugin->downloadEntity($smartling_entity, $locales);
  }

}
