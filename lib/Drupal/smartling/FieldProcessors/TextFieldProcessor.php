<?php

namespace Drupal\smartling\FieldProcessors;

class TextFieldProcessor extends BaseFieldProcessor {
  public function getSmartlingFormat() {
    $data = array();

    if (!empty($this->entity->{$this->fieldName}[$this->language])) {
      foreach ($this->entity->{$this->fieldName}[$this->language] as $delta => $value) {
        $data[$delta] = $value['value'];
      }
    }

    return $data;
  }

  public function getDrupalFormat() {
    $data = $this->entity->{$this->fieldName};

    foreach ($this->smartlingData[$this->fieldName][$this->language] as $delta => $value) {
      $data[$this->language][$delta]['value'] = $value;
    }

    return $data;
  }
}