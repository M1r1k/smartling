<?php

/**
 * @file
 * Contains Drupal\smartling\FieldProcessor\FieldProcessorFactory.
 *
 * @todo move to Drupal\smartling namespace.
 */

namespace Drupal\smartling;

use Drupal\smartling\ApiWrapper\SmartlingApiWrapper;
use Drupal\smartling\FieldProcessors\BaseFieldProcessor;
use Drupal\smartling\Log\SmartlingLog;

/**
 * Factory that creates field processor instances and contains mapping.
 *
 * @package Drupal\smartling\FieldProcessors
 */
class FieldProcessorFactory {

  protected $fieldMapping;

  /**
   * @param array $field_mapping_new
   * @param SmartlingLog $logger
   * @param SmartlingApiWrapper $smartling_api
   */
  public function __construct($field_mapping_new, $logger, $smartling_api) {
    $this->fieldMapping = $field_mapping_new;
  }

  /**
   * Factory method for FieldProcessor instances.
   *
   * @param string $field_name
   * @param \stdClass $entity
   * @param string $entity_type
   * @param \stdClass $smartling_entity
   * @param string $target_language
   * @param null|string $source_language
   *
   * @return BaseFieldProcessor
   */
  public function getProcessor($field_name, $entity, $entity_type, $smartling_entity, $target_language, $source_language = NULL) {
    $static_storage = &drupal_static(__CLASS__ . '_' . __METHOD__, array());

    $field_info = field_info_field($field_name);

    if ($field_info) {
      $type = $field_info['type'];
      // @todo we could get notice about invalid key here.
      $class_name = $this->fieldMapping['real'][$type];
    }
    elseif (isset($this->fieldMapping['fake'][$field_name])) {
      $type = $field_name;
      $class_name = $this->fieldMapping['fake'][$type];
    }
    else {
      $log = smartling_log_get_handler();
      $log->setMessage("Smartling found unexisted field - @field_name")
        ->setVariables(array('@field_name' => $field_name))
        ->setConsiderLog(FALSE)
        ->execute();

      return FALSE;
    }

    if (!$class_name) {
      $log = smartling_log_get_handler();
      $log->setMessage("Smartling didn't process content of field - @field_name")
        ->setVariables(array('@field_name' => $field_name))
        ->setConsiderLog(FALSE)
        ->execute();

      return FALSE;
    }

    $source_language = ($source_language ?: ((smartling_field_is_translatable_by_field_name($field_name, $entity_type)) ? entity_language($entity_type, $entity) : LANGUAGE_NONE));

    $field_class = new $class_name(
      $entity,
      $entity_type,
      $field_name,
      $smartling_entity,
      $source_language,
      $target_language
    );

    return $field_class;
  }

}