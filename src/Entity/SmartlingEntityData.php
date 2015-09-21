<?php

/**
 * @file
 * Contains \Drupal\smartling\Entity\SmartlingEntityData.
 */

namespace Drupal\smartling\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Language\LanguageInterface;
use Drupal\smartling\SmartlingEntityDataInterface;

/**
 * Defines the smartling entity class.
 *
 * @ContentEntityType(
 *   id = "smartling_entity_data",
 *   label = @Translation("Smartling Entity Data"),
 *   controllers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder"
 *   },
 *   base_table = "smartling_entity_data",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "eid"
 *   }
 * )
 */
class SmartlingEntityData extends ContentEntityBase implements SmartlingEntityDataInterface {

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values) {
    parent::__construct($values, 'smartling_entity_data');
  }

  /**
   * {@inheritdoc}
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

    $fields['translated_file'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('File with translations'))
      ->setDescription(t('Contains entity translated by Smartling.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'file')
      ->setSetting('handler', 'default')
      ->setTranslatable(FALSE);

    $fields['translated_file'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('File with translations'))
      ->setDescription(t('Contains entity translated by Smartling.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'file')
      ->setSetting('handler', 'default')
      ->setTranslatable(FALSE);

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

    $fields['status'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Smartling submission status'))
      ->setDescription(t('Smartling submission status'))
      ->setReadOnly(TRUE);

    $fields['content_hash'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Smartling submission status'))
      ->setDescription(t('Smartling submission status'))
      ->setReadOnly(TRUE);

    $fields['submission_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Smartling submission status'))
      ->setDescription(t('Smartling submission status'))
      ->setReadOnly(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedEntity() {
    // @todo add at least static caching here.
    return entity_load($this->get('entity_type')->value, $this->get('rid')->value);
  }

  public function setStatusByEvent($event) {
    if (is_null($event)) {
      return;
    }

    $status = $this->get('status')->value;
    switch ($event) {
      case SMARTLING_STATUS_EVENT_SEND_TO_UPLOAD_QUEUE:
        if (empty($status) || ($status == SMARTLING_STATUS_CHANGE)) {
          $status = SMARTLING_STATUS_IN_QUEUE;
        }
        break;

      case SMARTLING_STATUS_EVENT_UPLOAD_TO_SERVICE:
        $status = SMARTLING_STATUS_IN_TRANSLATE;
        break;

      case SMARTLING_STATUS_EVENT_DOWNLOAD_FROM_SERVICE:
      case SMARTLING_STATUS_EVENT_UPDATE_FIELDS:
        if ($status != SMARTLING_STATUS_CHANGE && $this->get('progress')->value == 100) {
          $status = SMARTLING_STATUS_TRANSLATED;
        }
        break;

      case SMARTLING_STATUS_EVENT_NODE_ENTITY_UPDATE:
        $status = SMARTLING_STATUS_CHANGE;
        break;

      case SMARTLING_STATUS_EVENT_FAILED_UPLOAD:
        $status = SMARTLING_STATUS_FAILED;
        break;
    }

    $this->set('status', $status);
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromDrupalEntity(ContentEntityInterface $entity, LanguageInterface $target_language) {
    $smartling_entity = self::create([
      'rid' => $entity->id(),
      'entity_type' => $entity->getEntityType()->id(),
      'bundle' => $entity->bundle(),
      'title' => $entity->label(),
      'original_language' => $entity->language()->getId(),
      'target_language' => $target_language->getId(),
      'submitter' => \Drupal::currentUser()->id(),
    ]);

    $smartling_entity->set('file_name', self::generateXmlFileName($smartling_entity));
    $smartling_entity->setStatusByEvent(0);

    return $smartling_entity;
  }

  public static function generateXmlFileName(SmartlingEntityDataInterface $entity) {
    return strtolower(trim(preg_replace('#\W+#', '_', $entity->get('title')->value), '_')) . '_' . $entity->get('entity_type')->value . '_' . $entity->get('rid')->value . '.xml';
  }

  /**
   * {@inheritdoc}
   */
  public static function loadMultipleByConditions(array $conditions) {
    $query = \Drupal::entityQuery('smartling_entity_data');
    foreach ($conditions as $name => $value) {
      $query = $query->condition($name, $value);
    }

    $ids = $query->execute();
    return static::loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public static function loadByConditions(array $conditions) {
    $entities = static::loadMultipleByConditions($conditions);
    return reset($entities);
  }

}
