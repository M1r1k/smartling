<?php

/**
 * @file
 * Contains \Drupal\smartling\Plugin\smartling\Source\ContentEntitySource.
 */

namespace Drupal\smartling\Plugin\smartling\Source;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\Type\StringInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\smartling\Entity\SmartlingEntityData;
use Drupal\smartling\SmartlingEntityDataInterface;
use Drupal\smartling\SourcePluginBase;
use Drupal\Core\Render\Element;
use Exception;

/**
 * Content entity source plugin controller.
 *
 * @SourcePlugin(
 *   id = "content",
 *   label = @Translation("Content Entity"),
 *   description = @Translation("Source handler for entities.")
 * )
 */
class ContentEntitySource extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getLabel(SmartlingEntityDataInterface $smartling_item) {
    if ($entity = $smartling_item->getRelatedEntity()) {
      return $entity->label();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(SmartlingEntityDataInterface $smartling_item) {
    if ($entity = $smartling_item->getRelatedEntity()) {
      return $entity->urlInfo();
    }
  }

  /**
   * Returns the data from the fields as a structure that can be processed by
   * the Smartling.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   SmartlingData entity.
   *
   * @return array
   *
   * @throws \Exception
   */
  public function getData(SmartlingEntityDataInterface $smartling_item) {
    $entity = $smartling_item->getRelatedEntity();
    if (!$entity) {
      // @todo provide own exceptions.
      throw new Exception(t('Unable to load entity %type with id %id', array('%type' => $smartling_item->get('entity_type')->value, $smartling_item->get('rid')->value)));
    }
    // @todo inject through the DI.
    $languages = \Drupal::languageManager()->getLanguages();
    $id = $entity->language()->getId();
    if (!isset($languages[$id])) {
      throw new Exception(t('Entity %entity could not be translated because the language %language is not applicable', array('%entity' => $entity->language()->getId(), '%language' => $entity->language()->getName())));
    }

    if (!$entity->hasTranslation($smartling_item->get('original_language')->value)) {
      throw new Exception(t('The entity %id with translation %lang does not exist.', array('%id' => $entity->id(), '%lang' => $smartling_item->getOriginalLanguageCode())));
    }

    $translation = $entity->getTranslation($smartling_item->get('original_language')->value);
    $data = $this->extractTranslatableData($translation);
    return $data;
  }

  /**
   * Extracts translatable data from an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to get the the translatable data from.
   *
   * @return array $data
   *   Translatable data.
   */
  public function extractTranslatableData(ContentEntityInterface $entity) {

    // @todo Expand this list or find a better solution to exclude fields like
    //   content_translation_source.

    $field_definitions = $entity->getFieldDefinitions();
    $exclude_field_types = ['language'];
    $translatable_fields = array_filter($field_definitions, function (FieldDefinitionInterface $field_definition) use ($exclude_field_types) {
        return $field_definition->isTranslatable() && !in_array($field_definition->getType(), $exclude_field_types);
    });

    $data = array();
    /* @var FieldDefinitionInterface $field_definition */
    foreach ($translatable_fields as $key => $field_definition) {
      $field = $entity->get($key);
      foreach ($field as $index => $field_item) {
        $format = NULL;
        /* @var FieldItemInterface $field_item */
        foreach ($field_item->getProperties() as $property_key => $property) {
          // Ignore computed values.
          $property_definition = $property->getDataDefinition();
          // Ignore values that are not primitives.
          if (!($property instanceof PrimitiveInterface)) {
            continue;
          }
          $translate = TRUE;
          // Ignore properties with limited allowed values or if they're not strings.
          if ($property instanceof OptionsProviderInterface || !($property instanceof StringInterface)) {
            $translate = FALSE;
          }
          // All the labels are here, to make sure we don't have empty labels in
          // the UI because of no data.
          if ($translate == TRUE) {
            $data[$key]['#label'] = $field_definition->getName();
            $data[$key][$index]['#label'] = t('Delta #@delta', array('@delta' => $index));
          }
          $data[$key][$index][$property_key] = array(
            '#label' => $property_definition->getLabel(),
            '#text' => $property->getValue(),
            '#translate' => $translate,
          );

          if ($property_definition->getDataType() == 'filter_format') {
            $format = $property->getValue();
          }
        }
        // Add the format to the translatable properties.
        if (!empty($format)) {
          foreach ($data[$key][$index] as $name => $value) {
            if (isset($value['#translate']) && $value['#translate'] == TRUE) {
              $data[$key][$index][$name]['#format'] = $format;
            }
          }
        }
      }
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function saveTranslation(SmartlingEntityDataInterface $smartling_item) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $smartling_item->getRelatedEntity();
    $file = $smartling_item->get('file_name')->value;
    // @todo process xml file and retrieve data.
    $xml = [];
    $this->doSaveTranslations($entity, $data, $smartling_item->get('target_language'));
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypes() {
    $entity_types = \Drupal::entityManager()->getDefinitions();
    $types = array();
    $content_translation_manager = \Drupal::service('content_translation.manager');
    foreach ($entity_types as $entity_type_name => $entity_type) {
      if ($content_translation_manager->isEnabled($entity_type->id())) {
        $types[$entity_type_name] = $entity_type->getLabel();
      }
    }
    return $types;
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypeLabel($type) {
    // @todo inject entity manager.
    return \Drupal::entityManager()->getDefinition($type)->getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function getType(SmartlingEntityDataInterface $smartling_item) {
    if ($entity = $smartling_item->getRelatedEntity()) {
      $bundles = \Drupal::entityManager()->getBundleInfo($smartling_item->get('entity_type')->value);
      $entity_type = $entity->getEntityType();
      $bundle = $entity->bundle();
      // Display entity type and label if we have one and the bundle isn't
      // the same as the entity type.
      if (isset($bundles[$bundle]) && $bundle != $smartling_item->get('entity_type')->value) {
        return t('@type (@bundle)', array('@type' => $entity_type->getLabel(), '@bundle' => $bundles[$bundle]['label']));
      }
      // Otherwise just display the entity type label.
      return $entity_type->getLabel();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceLangCode(SmartlingEntityDataInterface $smartling_item) {
    $entity = $smartling_item->getRelatedEntity();
    return $entity->getUntranslated()->language()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingLangCodes(SmartlingEntityDataInterface $smartling_item) {
    if ($entity = $smartling_item->getRelatedEntity()) {
      return array_keys($entity->getTranslationLanguages());
    }

    return array();
  }

  /**
   * Upload content from give smartling entity to smartling as file.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   Smartling entity object.
   * @param array $locales
   *   List of locales ask Smartling translate source entity to.
   */
  public function uploadEntity(SmartlingEntityDataInterface $smartling_item, array $locales) {
    $file_name = $smartling_item->get('file_name')->value;
    if (!$file_name) {
      $file_name = SmartlingEntityData::generateXmlFileName($smartling_item);
      $smartling_item->set('file_name', $file_name);
      $smartling_item->save();
    }

    // @todo inject service.
    $serializer = \Drupal::service('serializer');
    $data = $serializer->serialize($smartling_item->getRelatedEntity(), 'smartling_xml');
    $file = $this->saveFile($data, $file_name);

    // @todo inject it
    $api = \Drupal::service('smartling.api_wrapper');
    $api->uploadFile(drupal_realpath($file->getFileUri()), $file_name, 'xml', $locales);
  }

  /**
   * Download latest version of translation for given smartling entity.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_entity
   *   Smartling entity object.
   */
  public function downloadEntity(SmartlingEntityDataInterface $smartling_entity) {
    // @todo
  }

  /**
   * @param $data
   * @param $file_name
   *
   * @return \Drupal\file\FileInterface
   *
   * @todo throw exception if something is bad. Also add relation to smartling
   * entity.
   */
  public function saveFile($data, $file_name) {
    return file_save_data($data, 'private://' . $file_name);
  }

  /**
   * Saves translation data in an entity translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which the translation should be saved.
   * @param \Drupal\Core\Entity\ContentEntityInterface $translated_entity
   *   The translation data for the fields.
   * @param string $target_langcode
   *   The target language.
   */
  protected function doSaveTranslations(ContentEntityInterface $entity, ContentEntityInterface $translated_entity, $target_langcode) {
    // If the translation for this language does not exist yet, initialize it.
    if (!$entity->hasTranslation($target_langcode)) {
      $entity->addTranslation($target_langcode, $entity->toArray());
    }

    $translation = $entity->getTranslation($target_langcode);

    foreach ($translation as $name => $field_data) {
      foreach (Element::children($field_data) as $delta) {
        $field_item = $field_data[$delta];
        foreach (Element::children($field_item) as $property) {
          $property_data = $field_item[$property];
          // If there is translation data for the field property, save it.
          if (isset($property_data['#translation']['#text'])) {
            $translation->get($name)
              ->offsetGet($delta)
              ->set($property, $property_data['#translation']['#text']);
          }
        }
      }
    }
    $translation->save();
  }


  /**
   * Loads or creates the smartling entity for arguments.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language entity to translate.
   *
   * @return \Drupal\smartling\SmartlingEntityDataInterface
   *   The smartling entity.
   */
  public function getSmartlingEntityFromContentEntity(ContentEntityInterface $entity, LanguageInterface $language) {
    if ($smartling_entity = SmartlingEntityData::loadByConditions(['rid' => $entity->id(), 'target_language' => $language->getId()])) {
      return $smartling_entity;
    }

    return SmartlingEntityData::createFromDrupalEntity($entity, $language);
  }


}
