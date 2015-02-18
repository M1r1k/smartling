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
  public function add($entity_type, $entity, $langs) {
    $log = smartling_log_get_handler();

    $wrapper = entity_metadata_wrapper($entity_type, $entity);
    $id      = $wrapper->getIdentifier();
    $bundle  = $wrapper->getBundle();

    if (!smartling_translate_fields_configured($bundle, $entity_type)) {
      $link = smartling_get_link_to_entity($entity_type, $entity);

      drupal_set_message(t("Type '@type' is not supported or it's not configured in Smartling.", array('@type' => $bundle)), 'warning');
      $log->setMessage("Type '@type' is not supported or it's not configured in Smartling.")
        ->setVariables(array('@type' => $bundle))
        ->setConsiderLog(FALSE)
        ->setSeverity(WATCHDOG_ERROR)
        ->setLink($link)
        ->execute();

      return;
    }

    $queued_eids = array();
    switch ($entity_type) {
      case 'node':
        $node_status = NULL;
        if (smartling_nodes_method($entity->type)) {
          $node_status = smartling_nodes_method_node_status($entity);

          if (($entity->tnid == '0') && in_array($node_status, array(SMARTLING_ORIGINAL_NODE, SMARTLING_ORIGINAL_WITHOUT_TRANSLATION_NODE))) {
            $entity->tnid = $entity->nid;
            node_save($entity);
          }
        }
        break;

      case 'taxonomy_term':
        $language_default = language_default()->language;

        if (entity_language($entity_type, $entity) == $language_default) {
          $id = $entity->tid;
        }
        else {
          $original_term = smartling_get_original_taxonomy_term($entity);
          $id = $original_term->tid;
        }

        if (intval($id) == 0) {
          drupal_set_message(t('Original entity was not found. Please check if your current entity is "language neutral", that shouldn\'t be the case.'));
          return;
        }
        break;
    }

    // $d_locale_original = language_default()->language;
    // $d_locale_original = $entity->translations->original;
    $d_locale_original = entity_language($entity_type, $entity);
    foreach ($langs as $d_locale) {
      if ($d_locale == $d_locale_original) {
        continue;
      }

      if (!((smartling_nodes_method($entity->type) && in_array($node_status, array(SMARTLING_ORIGINAL_NODE, SMARTLING_ORIGINAL_WITHOUT_TRANSLATION_NODE)))
          || (smartling_fields_method($entity->type))) && ($entity_type == 'node')) {
        continue;
      }

      $smartling_data = smartling_entity_load_by_conditions(array(
        'rid' => $id,
        'entity_type' => $entity_type,
        'target_language' => $d_locale,
      ));

      if ($smartling_data == FALSE) {
        $smartling_data = smartling_create_from_entity($entity, $entity_type, $d_locale_original, $d_locale);
      }

      smartling_set_translation_status($smartling_data, SMARTLING_STATUS_EVENT_SEND_TO_UPLOAD_QUEUE);

      $queued_eids[] = $smartling_data->eid;
    }

    if (!empty($queued_eids)) {
      $smartling_queue = \DrupalQueue::get('smartling_upload');
      $smartling_queue->createQueue();
      $smartling_queue->createItem($queued_eids);
    }
    // Create content hash (Fake entity update).
    smartling_entity_update($entity, $entity_type);
  }

  /**
   * @inheritdoc
   */
  public function execute($eids) {
    if (!is_array($eids)) {
      $eids = array($eids);
    }

    $smartling_entity  = NULL;
    $target_locales    = array();
    $entity_data_array = array();

    $rids = array(); $types = array();
    foreach($eids as $eid) {
      $smartling_entity = entity_load_single('smartling_entity_data', $eid);
      $target_locales[] = $smartling_entity->target_language;
      $entity_data_array[] = $smartling_entity;

      $rids []= $smartling_entity->rid;
      $types []= $smartling_entity->entity_type;
    }

    if (count(array_unique($rids)) > 1 || count(array_unique($types)) > 1) {
      throw new \Exception('"eids" passed to the execute method point to different original entities.');
    }

    //$smartling_entity = entity_load_single('smartling_entity_data', $eid);

    if (!$smartling_entity || !smartling_is_configured()) {
      return;
    }

    $entity_type = $smartling_entity->entity_type;

    $processor = smartling_get_entity_processor($smartling_entity);
    $file_name = $processor->buildXmlFileName();
    $xml = smartling_build_xml($processor, $smartling_entity->rid);
    $success = FALSE;
    if ($xml instanceof \DOMNode) {
      $success = TRUE;
      foreach ($entity_data_array as $entity) {
        $success = (smartling_save_xml($xml, $entity, $file_name, FALSE))?$success:FALSE;
      }
    }

    if ($success) {
      $file_name_unic = $file_name;
      $file_path = drupal_realpath(smartling_clean_filename(smartling_get_dir($file_name), TRUE));

      // Init api object.
      $api = drupal_container()->get('smartling.api_wrapper');
      $result_status = $api->uploadFile($file_path, $file_name_unic, 'xml', $target_locales);

      //$processor->setProgressStatus($result_status);
      foreach ($entity_data_array as $entity) {
        $proc = smartling_get_entity_processor($entity);
        $proc->setProgressStatus($result_status);
      }

      if ($result_status == SMARTLING_STATUS_EVENT_UPLOAD_TO_SERVICE) {
        if (module_exists('rules') && ($entity_type == 'node')) {
          $node_event = node_load($smartling_entity->rid);
          rules_invoke_event('smartling_uploading_original_to_smartling_event', $node_event);
        }
      }
    }
    //@todo We lost this functionality in OOP branch, but it was introduced in 2.x. Must be restored
    else {
      foreach ($entity_data_array as $entity) {
        smartling_set_translation_status($entity, SMARTLING_STATUS_EVENT_FAILED_UPLOAD);
      }
    }
  }
}
