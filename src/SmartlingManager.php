<?php

/**
 * @file
 * Contains \Drupal\smartling\SmartlingManager.
 */

namespace Drupal\smartling;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\smartling\Entity\SmartlingEntityData;

/**
 * Manages queue for API calls.
 */
class SmartlingManager {

  /**
   * The smartling entity storage.
   *
   * @var \Drupal\Core\Entity\DynamicallyFieldableEntityStorageInterface
   */
  protected $storage;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * A logger instance.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a AccountInfoSettingsForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   The queue factory.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   A logger instance.
   */
  public function __construct(EntityManagerInterface $entity_manager, QueueFactory $queue, LoggerChannelInterface $logger) {
    $this->storage = $entity_manager->getStorage('smartling_entity_data');
    $this->queueFactory = $queue;
    $this->logger = $logger;
  }

  /**
   * Queues entities to check status.
   *
   * @param array $eids
   *   An array of smartling entity IDs.
   */
  public function addCheckStatusQueueWorker(array $eids) {
    /* @var \Drupal\smartling\SmartlingEntityDataInterface[] $entities */
    $entities = $this->storage->loadMultiple($eids);
    $queue = $this->queueFactory->get('smartling_check_status');

    foreach ($entities as $eid => $entity) {
      $file_name = $entity->getFileName();
      if (!empty($file_name)) {
        $queue->createItem($eid);
        $this->logger->info('Add item to "smartling_check_status" queue. Smartling entity data id - eid, related entity id - @rid, entity type - @entity_type',
          array('@id' => $entity->id(), '@rid' => $entity->getRelatedEntityId(), '@entity_type' => $entity->getEntityTypeId()));
      }
      elseif (!$entity->getStatus()) {
        $this->logger->warning('Original file name is empty. Smartling entity data id - @eid, related entity id - @rid, entity type - @entity_type',
          array('@eid' => $entity->id(), '@rid' => $entity->getRelatedEntityId(), '@entity_type' => $entity->getEntityTypeId()));
      }
    }
  }

  /**
   * Queues entities to download translation.
   *
   * @param array $eids
   *   An array of smartling entity IDs.
   */
  public function addDownloadQueueWorker(array $eids) {
    /* @var \Drupal\smartling\SmartlingEntityDataInterface[] $entities */
    $entities = $this->storage->loadMultiple($eids);
    $queue = $this->queueFactory->get('smartling_download');
    foreach ($entities as $eid => $entity) {
      $queue->createItem($eid);
    }
  }

  /**
   * Queues entities to download translation.
   *
   * @param array $eids
   *   An array of smartling entity IDs.
   */
  public function addUploadQueueWorker(array $eids) {
    $entities = $this->storage->loadMultiple($eids);
    $queue = $this->queueFactory->get('smartling_upload');
    foreach ($entities as $eid => $entity) {
      // @todo add support of multiple entities per worker.
      $queue->createItem($eid);
    }
  }

}
