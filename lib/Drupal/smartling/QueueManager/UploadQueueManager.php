<?php

/**
 * @file
 * Contains Drupal\smartling\Forms.
 */

namespace Drupal\smartling\QueueManager;

class UploadQueueManager implements QueueManagerInterface {
  /**
   * @inheritdoc
   */
  public function add($eids) {
    if (empty($eids)) {
      return FALSE;
    }
    $smartling_queue = \DrupalQueue::get('smartling_upload');
    $smartling_queue->createQueue();
    return $smartling_queue->createItem($eids);
  }

  /**
   * @inheritdoc
   */
  public function execute($eids) {
    if (!smartling_is_configured()) {
      return;
    }
    if (!is_array($eids)) {
      $eids = array($eids);
    }

    $smartling_entity  = NULL;
    $target_locales    = array();
    $entity_data_array = array();

    $entity_data_wrapper = drupal_container()->get('smartling.wrappers.entity_data_wrapper');
    foreach($eids as $eid) {
      $entity_data_wrapper->loadByID($eid);
      $file_name = $entity_data_wrapper->getFileName();
      $target_locales[$file_name][] = $entity_data_wrapper->getTargetLanguage();
      $entity_data_array[$file_name][] = $entity_data_wrapper->getEntity();
    }


    $api = drupal_container()->get('smartling.api_wrapper');
    foreach ($entity_data_array as $file_name => $entity_array) {
      $entity = reset($entity_array);
      $processor = smartling_get_entity_processor($entity);
      $xml = smartling_build_xml($processor, $entity->rid);
      if (!($xml instanceof \DOMNode)) {
        continue;
      }

      $event   = SMARTLING_STATUS_EVENT_FAILED_UPLOAD;
      $success = (bool) smartling_save_xml($file_name, $xml, $entity);
      // Init api object.
      if ($success) {
        $file_path = drupal_realpath(smartling_get_dir($file_name), TRUE);
        $event = $api->uploadFile($file_path, $file_name, 'xml', $target_locales[$file_name]);
      }

      foreach ($entity_array as $entity) {
        $entity_data_wrapper->setEntity($entity)->setStatusByEvent($event)->save();

        //@todo: refactor this code to be compatible with any entity_type
        if (($event == SMARTLING_STATUS_EVENT_UPLOAD_TO_SERVICE) && module_exists('rules') && ($entity_data_wrapper->getEntityType() == 'node')) {
            $node_event = node_load($entity_data_wrapper->getRID());
            rules_invoke_event('smartling_uploading_original_to_smartling_event', $node_event);
        }
      }
    }
  }
}
