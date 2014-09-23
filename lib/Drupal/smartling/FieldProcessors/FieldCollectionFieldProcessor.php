<?php

/**
 * @file
 * Contains Drupal\smartling\FieldProcessors\TitlePropertyFieldProcessor.
 */

namespace Drupal\smartling\FieldProcessors;

class FieldCollectionFieldProcessor extends BaseFieldProcessor {


  /**
   * Wrapper for Smartling settings storage.
   *
   * @todo avoid procedural code and inject storage to keep DI pattern.
   *
   * @return array()
   */
  protected function getTransletableFields() {
    //return smartling_settings_get_handler()->getFieldsSettings($this->entity->entity_type, $this->entity->bundle);
    return array('field_text1', 'field_some2');
  }

  /**
   * {@inheritdoc}
   */
  public function getSmartlingContent() {
    $data = array();

    //return $entity_current_translatable_content;
    if (!empty($this->entity->{$this->fieldName}[$this->sourceLanguage])) {
      foreach ($this->entity->{$this->fieldName}[$this->sourceLanguage] as $delta => $value) {
        $fid = (int)$value['value'];
        $entity = field_collection_item_load($fid);

        foreach ($this->getTransletableFields() as $field_name) {
          /* @var $fieldProcessor \Drupal\smartling\FieldProcessors\BaseFieldProcessor */
          $fieldProcessor = drupal_container()->get('smartling.field_processor_factory')->getProcessor($field_name, $entity, 'field_collection_item', $this->smartling_entity, $this->targetLanguage);

          if ($fieldProcessor) {
            $data[$fid][$field_name] = $fieldProcessor->getSmartlingContent();
          }
        }
      }
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getDrupalContent() {
    $data = $this->entity->{$this->fieldName};

    foreach ($this->smartling_entity[$this->fieldName][$this->sourceLanguage] as $delta => $value) {
      $data = $value;
    }

    return $data;
  }

  public function fetchDataFromXML(\DomXpath $xpath) {
    //@todo fetch format from xml as well.
    $result = array();
    $data = $xpath->query('//field_collection[@id="' . $this->fieldName . '"]')
      ->item(0);
    //echo $xpath->document;

    if (!$data) {
      return NULL;
    }

    $item = $data->firstChild;
    //$this->fetchDataFromXML($item);
    do {
      if ($item->tagName == 'string') {
        $eid = $item->attributes->getNamedItem('eid');
        $field = $item->attributes->getNamedItem('id');
        $delta = $item->attributes->getNamedItem('delta');

        $result[$eid->value][$field->value][$delta->value] = $item->nodeValue;
      }
    } while ($item = $item->nextSibling);

//    $quantity = $quantity_value->getAttribute('quantity');

//    for ($i = 0; $i < $quantity; $i++) {
//      $field = $xpath->query('//string[@id="' . $this->fieldName . '-' . $i . '"][1]')
//        ->item(0);
//      $data[$i]['value'] = $this->processXMLContent((string) $field->nodeValue);
//    }

    return $result;
  }

  public function prepareBeforeDownload(array $fieldData) {
    return $fieldData;
  }

  public function setDrupalContentFromXML($xpath) {

    $content = $this->fetchDataFromXML($xpath);

    $new_values = array();
    $old_values = $this->entity->{$this->fieldName}[$this->targetLanguage];
    foreach($old_values as $k => $id) {
      $val = next($content);
      $new_values[$k] = $this->saveContentToEntity($id, $val);
    }
    $this->entity->{$this->fieldName}[$this->targetLanguage] = $content;
  }

  protected function saveContentToEntity($id, $value) {
    $entity = field_collection_item_load($id);

    foreach($value as $field_name => $val) {
      $entity->{$field_name}[$entity->language] = $val;
    }

    //field_collection_item_save($entity);
    return $id;
  }

}


