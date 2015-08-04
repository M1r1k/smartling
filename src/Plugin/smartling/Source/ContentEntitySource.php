<?php

/**
 * @file
 * Contains Drupal\smartling\Plugin\smartling\Source\ContentEntitySource.
 */

namespace Drupal\smartling\Plugin\smartling\Source;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityReference;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\Type\StringInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\smartling\Annotation\SourcePlugin;
use Drupal\smartling\Entity\SmartlingEntityDataInterface;
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
   * @param \Drupal\smartling\Entity\SmartlingEntityDataInterface $smartling_item
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   */
  protected function getRelatedEntity(SmartlingEntityDataInterface $smartling_item) {
    return entity_load($smartling_item->getRelatedEntityTypeId(), $smartling_item->getRelatedEntityId());
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(SmartlingEntityDataInterface $smartling_item) {
    if ($entity = $this->getRelatedEntity($smartling_item)) {
      return $entity->label();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(SmartlingEntityDataInterface $smartling_item) {
    if ($entity = $this->getRelatedEntity($smartling_item)) {
      return $entity->urlInfo();
    }
  }

  /**
   * Returns the data from the fields as a structure that can be processed by
   * the Smartling.
   */
  public function getData(SmartlingEntityDataInterface $smartling_item) {
    $entity = $entity = $this->getRelatedEntity($smartling_item);
    if (!$entity) {
      // @todo provide own exceptions.
      throw new Exception(t('Unable to load entity %type with id %id', array('%type' => $smartling_item->getRelatedEntityTypeId(), $smartling_item->getRelatedEntityId())));
    }
    // @todo inject through the DI.
    $languages = \Drupal::languageManager()->getLanguages();
    $id = $entity->language()->getId();
    if (!isset($languages[$id])) {
      throw new Exception(t('Entity %entity could not be translated because the language %language is not applicable', array('%entity' => $entity->language()->getId(), '%language' => $entity->language()->getName())));
    }

    if (!$entity->hasTranslation($smartling_item->getOriginalLanguageCode())) {
      throw new Exception(t('The entity %id with translation %lang does not exist.', array('%id' => $entity->id(), '%lang' => $smartling_item->getOriginalLanguageCode())));
    }

    $translation = $entity->getTranslation($smartling_item->getOriginalLanguageCode());
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
    $entity = $this->getRelatedEntity($smartling_item);
    $file = $smartling_item->getFileName();
    // @todo process xml file and retrieve data.
    $data = [];
    $this->doSaveTranslations($entity, $data, $smartling_item->getTargetLanguageCode());
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
    return \Drupal::entityManager()->getDefinition($type)->getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function getType(SmartlingEntityDataInterface $smartling_item) {
    if ($entity = $this->getRelatedEntity($smartling_item)) {
      $bundles = \Drupal::entityManager()->getBundleInfo($smartling_item->getRelatedEntityTypeId());
      $entity_type = $entity->getEntityType();
      $bundle = $entity->bundle();
      // Display entity type and label if we have one and the bundle isn't
      // the same as the entity type.
      if (isset($bundles[$bundle]) && $bundle != $smartling_item->getRelatedEntityTypeId()) {
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
    $entity = $this->getRelatedEntity($smartling_item);
    return $entity->getUntranslated()->language()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingLangCodes(SmartlingEntityDataInterface $smartling_item) {
    if ($entity = $this->getRelatedEntity($smartling_item)) {
      return array_keys($entity->getTranslationLanguages());
    }

    return array();
  }

  /**
   * Saves translation data in an entity translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which the translation should be saved.
   * @param array $data
   *   The translation data for the fields.
   * @param string $target_langcode
   *   The target language.
   */
  protected function doSaveTranslations(ContentEntityInterface $entity, array $data, $target_langcode) {
    // If the translation for this language does not exist yet, initialize it.
    if (!$entity->hasTranslation($target_langcode)) {
      $entity->addTranslation($target_langcode, $entity->toArray());
    }

    $translation = $entity->getTranslation($target_langcode);

    foreach ($data as $name => $field_data) {
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
}
