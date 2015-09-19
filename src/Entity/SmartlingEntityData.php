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

    // @todo add other fields.

    return $fields;
  }

  public function getRelatedEntity() {
    // @todo add at least static caching here.
    return entity_load($this->getRelatedEntityTypeId(), $this->getRelatedEntityId());
  }

  public function getRelatedEntityId() {
    return $this->rid->value;
  }

  public function setRelatedEntityId($related_entity_id) {
    $this->rid->value = $related_entity_id;
  }

  public function getRelatedEntityTypeId() {
    return $this->entity_type->value;
  }

  public function setRelatedEntityTypeId($related_entity_type_id) {
    $this->entity_type->value = $related_entity_type_id;
  }

  public function getRelatedEntityBundleId() {
    return $this->bundle->value;
  }

  public function setRelatedEntityBundleId($related_entity_bundle_id) {
    $this->bundle->value = $related_entity_bundle_id;
  }

  public function getOriginalLanguageCode() {
    return $this->original_language->value;
  }

  public function setOriginalLanguageCode($language_code) {
    $this->original_language->value = $language_code;
  }

  public function getTargetLanguageCode() {
    return $this->target_language->value;
  }

  public function setTargetLanguageCode($language_code) {
    $this->target_language->value = $language_code;
  }

  public function getTitle() {
    return $this->title->value;
  }

  public function setTitle($title) {
    $this->title->value = $title;
  }

  public function getFileName() {
    return $this->file_name->value;
  }

  public function setFileName($file_name) {
    $this->file_name->value = $file_name;
  }

  public function getTranslatedFileName() {
    // TODO: Implement getTranslatedFileName() method.
  }

  public function setTranslatedFileName($file_name) {
    // TODO: Implement setTranslatedFileName() method.
  }

  public function getProgress() {
    // TODO: Implement getProgress() method.
  }

  public function setProgress() {
    // TODO: Implement setProgress() method.
  }

  public function getSubmitter() {
    // TODO: Implement getSubmitter() method.
  }

  public function setSubmitter($submitter) {
    // TODO: Implement setSubmitter() method.
  }

  public function getSubmissionDate() {
    // TODO: Implement getSubmissionDate() method.
  }

  public function getDownloadStatus() {
    // TODO: Implement getDownloadStatus() method.
  }

  public function getStatus() {
    return $this->status->value;
  }

  public function getContentHash() {
    // TODO: Implement getContentHash() method.
  }

  public function setStatusByEvent($event) {
    if (is_null($event)) {
      return;
    }

    $status = $this->getStatus();
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
        if ($status != SMARTLING_STATUS_CHANGE && $this->getProgress() == 100) {
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

    $this->setStatus($status);
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromDrupalEntity(ContentEntityInterface $entity, LanguageInterface $target_language) {
    $entity = new static([
      'rid' => $entity->id(),
      'entity_type' => $entity->getEntityType(),
      'bundle' => $entity->bundle(),
      'original_language' => $entity->language()->getId(),
      // @todo handle exception when target language invalid or not configured
      // for given entity.
      'target_language' => $target_language->getId(),
      'title' => $entity->label(),
      'submitter' => \Drupal::currentUser()->id(),
      'submission_date' => REQUEST_TIME,
      'download' => '',
      'status' => 0,
      'content_hash' => ''
    ]);

    $entity->setFileName(self::generateXmlFileName($entity));

    return ;
  }

  public static function generateXmlFileName(SmartlingEntityDataInterface $entity) {
    return strtolower(trim(preg_replace('#\W+#', '_', $entity->getTitle()), '_')) . '_' . $entity->getEntityType() . '_' . $entity->getRID() . '.xml';
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
