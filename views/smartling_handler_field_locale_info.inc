<?php

/**
 * @file
 * Handler code.
 */

/**
 * Handler for field locale_info.
 */
class smartling_handler_field_locale_info extends views_handler_field {

  function render($values) {
    if (isset($values->nid)) {
      $result = smartling_entity_load_all_by_conditions(array('rid' => $values->nid, 'entity_type' => 'node'));
    }
    elseif (isset($values->tid)) {
      $result = smartling_entity_load_all_by_conditions(array('rid' => $values->tid, 'entity_type' => 'taxonomy_term'));
    }
    elseif (isset($values->cid)) {
      $result = smartling_entity_load_all_by_conditions(array('rid' => $values->cid, 'entity_type' => 'comment'));
    }
    elseif (isset($values->uid)) {
      $result = smartling_entity_load_all_by_conditions(array('rid' => $values->uid, 'entity_type' => 'user'));
    }
    else {
      $result = FALSE;
    }
    if ($result) {
      foreach ($result as $value) {
        $langs[] = smartling_get_full_language_name($value->target_language);
      }
      return implode(', ', $langs);
    }
    else {
      return '';
    }
  }

}