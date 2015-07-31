<?php

namespace Drupal\smartling\Entity;

use Drupal\Core\Entity\EntityInterface;

interface SmartlingEntityDataInterface extends EntityInterface {

  public function getRelatedEntityId();

  public function setRelatedEntityId($related_entity_id);

  public function getRelatedEntityTypeId();

  public function setRelatedEntityTypeId($related_entity_type_id);

  public function getRelatedEntityBundleId();

  public function setRelatedEntityBundleId($related_entity_bundle_id);

  public function getOriginalLanguageCode();

  public function setOriginalLanguageCode($language_code);

  public function getTargetLanguageCode();

  public function setTargetLanguageCode($language_code);

  public function getTitle();

  public function setTitle($title);

  public function getFileName();

  public function setFileName($file_name);

  public function getTranslatedFileName();

  public function setTranslatedFileName($file_name);

  public function getProgress();

  public function setProgress();

  public function getSubmitter();

  public function setSubmitter($submitter);

  public function getSubmissionDate();

  public function getDownloadStatus();

  public function getStatus();

  public function getContentHash();

}
