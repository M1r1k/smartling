<?php

namespace Drupal\smartling;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\smartling\Entity\SmartlingEntityData;
use Drupal\smartling\Entity\SmartlingEntityDataInterface;

class SmartlingManager {
  // @todo inject all dependencies
  public function addCheckStatusQueueWorker(array $eids) {
    /* @var SmartlingEntityDataInterface[] $entities */
    $entities = SmartlingEntityData::loadMultiple($eids);
    $queue = \Drupal::queue('smartling_check_status');

    foreach ($entities as $eid => $entity) {
      $file_name = $entity->getFileName();
      if (!empty($file_name)) {
        $queue->createItem($eid);
        \Drupal::logger('smartling')->info('Add item to "smartling_check_status" queue. Smartling entity data id - eid, related entity id - @rid, entity type - @entity_type',
          array('@id' => $entity->id(), '@rid' => $entity->getRelatedEntityId(), '@entity_type' => $entity->getEntityTypeId()));
      }
      elseif (!$entity->getStatus()) {
        $this->log->warning('Original file name is empty. Smartling entity data id - @eid, related entity id - @rid, entity type - @entity_type',
          array('@eid' => $entity->id(), '@rid' => $entity->getRelatedEntityId(), '@entity_type' => $entity->getEntityTypeId()));
      }
    }
  }

  public function addDownloadQueueWorker(array $eids) {
    /* @var SmartlingEntityDataInterface[] $entities */
    $entities = SmartlingEntityData::loadMultiple($eids);
    $queue = \Drupal::queue('smartling_download');
    foreach ($entities as $eid => $entity) {
      $queue->createItem($eid);
    }
  }

  public function addUploadQueueWorker(array $eids) {
    /* @var SmartlingEntityDataInterface[] $entities */
    $entities = SmartlingEntityData::loadMultiple($eids);
    $queue = \Drupal::queue('smartling_upload');
    foreach ($entities as $eid => $entity) {
      // @todo add support of multiple entities per worker.
      $queue->createItem($eid);
    }
  }

  public function getSmartlingEntityFromContentEntity(ContentEntityInterface $entity, LanguageInterface $language) {
    if ($smartling_entity = SmartlingEntityData::loadByConditions(['rid' => $entity->id(), 'target_language' => $language->getId()])) {
      return $smartling_entity;
    }

    return SmartlingEntityData::createFromDrupalEntity($entity, $language);
  }

}
