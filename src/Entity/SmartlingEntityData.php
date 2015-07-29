<?php

/**
 * @file
 * Contains \Drupal\smartling\Entity\SmartlingEntityData.
 */

namespace Drupal\smartling\Entity;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the node entity class.
 *
 * @ContentEntityType(
 *   id = "smartling_entity_data",
 *   label = @Translation("Smartling Entity Data"),
 *   controllers = {
 *     "storage" = "Drupal\smartling\SmartlingStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder"
 *   },
 *   base_table = "smartling_entity_data",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "eid"
 *   }
 *
 * )
 */
class SmartlingEntityData extends ContentEntityBase {
  /**
   * Provides base field definitions for an entity type.
   *
   * Implementations typically use the class
   * \Drupal\Core\Field\BaseFieldDefinition for creating the field definitions;
   * for example a 'name' field could be defined as the following:
   * @code
   * $fields['name'] = BaseFieldDefinition::create('string')
   *   ->setLabel(t('Name'));
   * @endcode
   *
   * By definition, base fields are fields that exist for every bundle. To
   * provide definitions for fields that should only exist on some bundles, use
   * \Drupal\Core\Entity\FieldableEntityInterface::bundleFieldDefinitions().
   *
   * The definitions returned by this function can be overridden for all
   * bundles by hook_entity_base_field_info_alter() or overridden on a
   * per-bundle basis via 'base_field_override' configuration entities.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition. Useful when a single class is used for multiple,
   *   possibly dynamic entity types.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of base field definitions for the entity type, keyed by field
   *   name.
   *
   * @see \Drupal\Core\Entity\EntityManagerInterface::getFieldDefinitions()
   * @see \Drupal\Core\Entity\FieldableEntityInterface::bundleFieldDefinitions()
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['eid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Smartling submission ID'))
      ->setDescription(t('The smartling submission entity ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The smarting entity UUID.'))
      ->setReadOnly(TRUE);

    $fields['rid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Smartling submission\'s related entity ID'))
      ->setDescription(t('The entity smartling submission stores translation of.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Smartling submission\'s related entity type machine name'))
      ->setDescription(t('Smartling submission\'s related entity type machine name.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Smartling submission\'s related entity bundle machine name'))
      ->setDescription(t('Smartling submission\'s related entity bundle machine name.'))
      ->setReadOnly(TRUE);

    $fields['original_language'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Original language code'))
      ->setDescription(t('Original language code (drupal format).'))
      ->setReadOnly(TRUE)
      ->setTranslatable(FALSE);

    $fields['target_language'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Target language code'))
      ->setDescription(t('Target language code (drupal format).'))
      ->setReadOnly(TRUE)
      ->setReadOnly(TRUE)
      ->setTranslatable(FALSE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Smartling submission entity title'))
      ->setDescription(t('Smartling submission entity title. Usually it is equal to related entity title.'))
      ->setReadOnly(TRUE);

    $fields['file_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('File with original content'))
      ->setDescription(t('File with original content.'))
      ->setReadOnly(TRUE);

    $fields['translated_file_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('File with translated content'))
      ->setDescription(t('File with translated content.'))
      ->setReadOnly(TRUE);

    $fields['progress'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Smartling submission translation progress'))
      ->setDescription(t('Smartling submission translation progress.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['submitter'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Smartling submission submitter'))
      ->setDescription(t('Smartling submission submitter'))
      ->setReadOnly(TRUE);

    // @todo add other fields.

    return $fields;
  }
}
