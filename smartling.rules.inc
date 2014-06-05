<?php

/**
 * @file
 * Rules functions for Smartling module.
 */

/**
 * Implementation of hook_rules_event_info()
 */
function smartling_rules_event_info() {
  return array(
    'smartling_callback_call_event' => array(
      'label' => t('After handling callback from Smartling'),
      'module' => 'smartling',
      'group' => 'smartling',
      'variables' => array(
        'locale' => array('type' => 'text', 'label' => t('Locale')),
        'file_uri' => array('type' => 'text', 'label' => t('File uri')),
      ),
    ),
    'smartling_translation_download_event' => array(
      'label' => t('After downloading translation from Smartling'),
      'module' => 'smartling',
      'group' => 'smartling',
      'variables' => array(
        'node' => array('type' => 'node', 'label' => t('Translated node')),
      ),
    ),
    'smartling_uploading_original_to_smartling_event' => array(
      'label' => t('After uploading original content to Smartling'),
      'module' => 'smartling',
      'group' => 'smartling',
      'variables' => array(
        'node' => array('type' => 'node', 'label' => t('Uploaded node')),
      ),
    ),
  );
}

/**
 * Implements hook_rules_condition_info().
 */
function smartling_rules_condition_info() {
  return array(
    'smartling_locale_condition' => array(
      'label' => t('Locale comparison'),
      'parameter' => array(
        'data' => array(
          'type' => '*',
          'label' => t('Locale to compare'),
          'description' => t('The locale to be compared, specified by using a data selector, e.g. "locale".'),
          'allow null' => TRUE,
        ),
        'op' => array(
          'type' => 'text',
          'label' => t('Operator'),
          'description' => t('The comparison operator.'),
          'optional' => TRUE,
          'default value' => '==',
          'options list' => 'rules_condition_data_is_operator_options',
          'restriction' => 'input',
        ),
        'value' => array(
          'type' => 'text',
          'label' => t('Locale value'),
          'description' => t('The value to compare the locale with.'),
        ),
      ),
      'group' => t('Smartling'),
    ),
  );
}

/**
 * Implements hook_rules_action_info().
 */
function smartling_rules_action_info() {
  $actions = array(
    'smartling_get_translation_status_action' => array(
      'label' => t('Get translation status from Smartling'),
      'group' => t('Smartling actions'),
      'parameter' => array(
        'node' => array(
          'type' => 'node',
          'label' => t('Node'),
          'optional' => FALSE,
          'description' => t('There should be selected node object.'),
        ),
        'translate_to_langs' => array(
          'type' => 'list<text>',
          'label' => t('Languages'),
          'optional' => TRUE,
          'options list' => 'smartling_language_options_list',
          'description' => t('Translations will be checked for selected languages.'),
        ),
      ),
    ),
    'smartling_download_translation_action' => array(
      'label' => t('Download translation from Smartling'),
      'group' => t('Smartling actions'),
      'parameter' => array(
        'node' => array(
          'type' => 'node',
          'label' => t('Node'),
          'optional' => FALSE,
          'description' => t('There should be selected node object.'),
        ),
        'translate_to_langs' => array(
          'type' => 'list<text>',
          'label' => t('Languages'),
          'optional' => TRUE,
          'options list' => 'smartling_language_options_list',
          'description' => t('Translations will be downloaded for selected languages.'),
        ),
      ),
    ),
    'smartling_send_to_translate_action' => array(
      'label' => t('Upload original content to Smartling'),
      'group' => t('Smartling actions'),
      'parameter' => array(
        'node' => array(
          'type' => 'node',
          'label' => t('Node'),
          'optional' => FALSE,
          'description' => t('There should be selected node object.'),
        ),
        'translate_to_langs' => array(
          'type' => 'list<text>',
          'label' => t('Languages'),
          'optional' => TRUE,
          'options list' => 'smartling_language_options_list',
          'description' => t('Node will be translated into the selected languages.'),
        ),
      ),
    ),
  );

  return $actions;
}

function smartling_locale_condition($data, $op, $value) {
  switch ($op) {
    default:
    case '==':
      // In case both values evaluate to FALSE, further differentiate between
      // NULL values and values evaluating to FALSE.
      if (!$data && !$value) {
        return (isset($data) && isset($value)) || (!isset($data) && !isset($value));
      }
      return $data == $value;
    case '<':
      return $data < $value;
    case '>':
      return $data > $value;
    // Note: This is deprecated by the text comparison condition and IN below.
    case 'contains':
      return is_string($data) && strpos($data, $value) !== FALSE || is_array($data) && in_array($value, $data);
    case 'IN':
      return is_array($value) && in_array($data, $value);
  }
}

/**
 * Get translation status.
 * @param type $node
 */
function smartling_get_translation_status_action($node, $translate_to_langs) {
  $log = new SmartlingLog();
  if (is_object($node) && !empty($translate_to_langs)) {
    foreach ($translate_to_langs as $d_locale) {
      $entity_data = smartling_entity_load_by_conditions(array(
        'rid' => $node->nid,
        'entity_type' => 'node',
        'target_language' => $d_locale
      ));
      if ($entity_data) {
        if (!empty($entity_data->rid) && !empty($entity_data->target_language)) {
          $args = new stdClass;
          $args->rid = $entity_data->rid;
          $args->d_locale = $entity_data->target_language;
          $log->setMessage('Smartling start check status action for node id - @nid (@d_locale).')
            ->setVariables(array('@nid' => $args->rid, '@d_locale' => $args->d_locale))
            ->setLink(l(t('Edit node'), 'node/' . $node->nid . '/edit'))
            ->execute();
          smartling_queue_check_status_process($args);
          unset($args);
        }
      }
    }
  }
  elseif (is_object($node) && empty($translate_to_langs)) {
    // No selected languages
    $result = smartling_entity_load_all_by_conditions(array('rid' => $node->nid, 'entity_type' => 'node'));
    if ($result) {
      foreach ($result as $entity_data) {
        if (!empty($entity_data->rid) && !empty($entity_data->target_language)) {
          $args = new stdClass;
          $args->rid = $entity_data->rid;
          $args->d_locale = $entity_data->target_language;
          $log->setMessage('Smartling start check status action for node id - @nid (@d_locale).')
            ->setVariables(array('@nid' => $args->rid, '@d_locale' => $args->d_locale))
            ->setLink(l(t('Edit node'), 'node/' . $node->nid . '/edit'))
            ->execute();
          smartling_queue_check_status_process($args);
          unset($args);
        }
      }
    }
  }
}

function smartling_download_translation_action($node, $translate_to_langs) {
  $log = new SmartlingLog();
  if (is_object($node) && !empty($translate_to_langs)) {
    foreach ($translate_to_langs as $d_locale) {
      $entity_data = smartling_entity_load_by_conditions(array(
        'rid' => $node->nid,
        'entity_type' => 'node',
        'target_language' => $d_locale
      ));
      if ($entity_data) {
        $log->setMessage('Smartling start download translation action for node id - @nid (@d_locale).')
          ->setVariables(array(
            '@nid' => $entity_data->rid,
            '@d_locale' => $entity_data->target_language
          ))
          ->setLink(l(t('Edit node'), 'node/' . $node->nid . '/edit'))
          ->execute();
        smartling_queue_download_update_translated_item_process($entity_data);
      }
    }
  }
  elseif (is_object($node) && empty($translate_to_langs)) {
    // No selected languages
    $result = smartling_entity_load_all_by_conditions(array('rid' => $node->nid, 'bundle' => $node->type));
    if ($result) {
      foreach ($result as $entity_data) {
        $log->setMessage('Smartling start download translation action for node id - @nid (@d_locale).')
          ->setVariables(array('@nid' => $entity_data->rid, '@d_locale' => $entity_data->target_language))
          ->setLink(l(t('Edit node'), 'node/' . $node->nid . '/edit'))
          ->execute();
        smartling_queue_download_update_translated_item_process($entity_data);
      }
    }
  }
}

function smartling_send_to_translate_action($node, $translate_to_langs) {
  global $user;
  $log = new SmartlingLog();

  if (is_object($node) && !empty($translate_to_langs)) {
    foreach ($translate_to_langs as $d_locale) {

      if (smartling_supported_type('node', $node->type) && smartling_translate_fields_is_set($node->type)) {

        if (smartling_nodes_method($node->type)) {
          $status = smartling_nodes_method_node_status($node);
          if ($status == SMARTLING_ORIGINAL_NODE || $status == SMARTLING_ORIGINAL_WITHOUT_TRANSLATION_NODE) {
            if ($node->tnid == '0') {
              $node->tnid = $node->nid;
              node_save($node);
            }
            if ($d_locale != entity_language('node', $node)) {
              $d_locales[] = $d_locale;
            }
          }
        }
        elseif (smartling_fields_method($node->type)) {
          if ($d_locale != entity_language('node', $node)) {
            $d_locales[] = $d_locale;
          }
        }
      }
    }
    $obj = new stdClass;
    $obj->rid = $node->nid;
    $obj->type = $node->type;
    $obj->title = $node->title;
    $obj->original_language = entity_language('node', $node);
    $obj->d_locales = $d_locales;
    $obj->submitter = $user->uid;
    $log->setMessage('Smartling start send to translate action for node id - @nid (@d_locales).')
      ->setVariables(array('@nid' => $obj->rid, '@s_locales' => implode('; ', $d_locales)))
      ->setLink(l(t('Edit node'), 'node/' . $node->nid . '/edit'))
      ->execute();
    smartling_queue_send_to_translate_process($obj);
    unset($obj);
  }
  elseif (is_object($node) && empty($translate_to_langs)) {
    // No selected languages
    $result = smartling_entity_load_all_by_conditions(array('rid' => $node->nid, 'bundle' => $node->type));
    if ($result) {
      foreach ($result as $entity_data) {
        $d_locale = $entity_data->target_language;
        if (smartling_supported_type('node', $node->type) && smartling_translate_fields_is_set($node->type)) {
          if (smartling_nodes_method($node->type)) {
            $status = smartling_nodes_method_node_status($node);
            if ($status == SMARTLING_ORIGINAL_NODE || $status == SMARTLING_ORIGINAL_WITHOUT_TRANSLATION_NODE) {
              if ($node->tnid == '0') {
                $node->tnid = $node->nid;
                node_save($node);
              }
              if ($d_locale != entity_language('node', $node)) {
                $d_locales[] = $d_locale;
              }
            }
          }
          elseif (smartling_fields_method($node->type)) {
            if ($d_locale != entity_language('node', $node)) {
              $d_locales[] = $d_locale;
            }
          }
        }
      }
      $obj = new stdClass;
      $obj->rid = $node->nid;
      $obj->type = $node->type;
      $obj->title = $node->title;
      $obj->original_language = entity_language('node', $node);
      $obj->d_locales = $d_locales;
      $obj->submitter = $user->uid;
      $log->setMessage('Smartling start send to translate action for node id - @nid (@s_locales).')
        ->setVariables(array('@nid' => $obj->rid, '@s_locales' => implode('; ', $d_locales)))
        ->setLink(l(t('Edit node'), 'node/' . $node->nid . '/edit'))
        ->execute();
      smartling_queue_send_to_translate_process($obj);
      unset($obj);
    }
  }
}