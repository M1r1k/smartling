<?php

/**
 * @file
 * Contains \Drupal\locale\Plugin\QueueWorker\LocaleTranslation.
 */

namespace Drupal\smartling\Plugin\QueueWorker;

use Drupal\Core\Annotation\QueueWorker;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
 *   id = "smartling_download",
 *   title = @Translation("Download files with translations"),
 *   cron = {"time" = 30}
 * )
 */
class SmartlingDownload extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

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
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, LoggerChannelInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.channel.smartling')
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
      $smartling_data_ids = array($smartling_data_ids);
    }
    $entities = SmartlingEntityData::loadMultiple($smartling_data_ids);

    $global_status = TRUE;
    foreach ($entities as $entity) {
      $status = FALSE;

      $downloaded_content = $this->file_transport->download($entity);
      if ($downloaded_content) {
        $processor = $this->entity_processor_factory->getProcessor($entity);
        $status = $processor->updateEntity($entity);
      }
//      $this->drupal_wrapper->rulesIvokeEvent('smartling_after_submission_download_event', array($eid));
      $global_status = $global_status & $status;
    }

    return $global_status;
  }

}
