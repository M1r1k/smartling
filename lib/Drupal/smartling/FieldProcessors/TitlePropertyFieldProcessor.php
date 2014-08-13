<?php

namespace Drupal\smartling\FieldProcessors;

class TitlePropertyFieldProcessor extends BaseFieldProcessor {
  public function getSmartlingFormat() {
//    $data = array();

//    if (!empty($this->entity->title)) {
//      $data[0] = $this->entity->label();
//    }

    return array(entity_label($this->entityType, $this->entity));
  }

  public function getDrupalFormat() {
    $data = $this->entity->{$this->fieldName};

    foreach ($this->smartlingData[$this->fieldName][$this->language] as $delta => $value) {
      $data = $value;
    }

    return $data;
  }


}