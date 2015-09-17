<?php

/**
 * @file
 * Contains \Drupal\smartling\Plugin\QueueWorker\SmartlingCheckStatus.
 */

namespace Drupal\smartling\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\smartling\Entity\SmartlingEntityData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interface translation queue tasks.
 *
 * @QueueWorker(
 *   id = "smartling_check_status",
 *   title = @Translation("Check smartling translation status"),
 *   cron = {"time" = 30}
 * )
 */
class SmartlingCheckStatus extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The queue object.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * Constructs a new LocaleTranslation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The module handler.
   * @param \Drupal\Core\Queue\QueueInterface $queue
   *   The queue object.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, LoggerChannelInterface $logger, QueueInterface $queue) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->logger = $logger;
    $this->queue = $queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.channel.smartling'),
      $container->get('queue')->get('smartling_download', TRUE)
    );
  }

  /**
   * {@inheritdoc}
   *
   * The translation update functions executed here are batch operations which
   * are also used in translation update batches. The batch functions may need
   * to be executed multiple times to complete their task, typically this is the
   * translation import function. When a batch function is not finished, a new
   * queue task is created and added to the end of the queue. The batch context
   * data is needed to continue the batch task is stored in the queue with the
   * queue data.
   */
  public function processItem($smartling_data_ids) {
    if (!is_array($smartling_data_ids)) {
      $smartling_data_ids = [$smartling_data_ids];
    }
    $entities = SmartlingEntityData::loadMultiple($smartling_data_ids);

    $result = array();
    foreach($entities as $smartling_submission) {
      $request_result = $this->apiWrapper->getStatus($smartling_submission);
      if (empty($request_result)) {
        continue;
      }

      $result[$eid] = $request_result;
      if (($request_result['response_data']->approvedStringCount == $request_result['response_data']->completedStringCount)
        && ($smartling_submission->getRelatedEntityTypeId() != 'smartling_interface_entity')) {
        $this->queue->createItem($eid);
      }

      $smartling_submission->setEntity($request_result['entity_data'])->save();

//      $this->drupal_wrapper->rulesIvokeEvent('smartling_after_submission_check_status_event', array($eid));

    }

    return $result;
  }

}
