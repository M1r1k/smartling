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
 *   id = "smartling_upload",
 *   title = @Translation("Uploads file to smartling to be translated"),
 *   cron = {"time" = 30}
 * )
 */
class SmartlingUpload extends QueueWorkerBase implements ContainerFactoryPluginInterface {

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

    foreach ($entities as $entity) {
      $file_name = $entity->getFileName();
      $target_locales[$file_name][] = $entity->getTargetLanguageCode();
      $entity_data_array[$file_name][] = $entity;
    }

    foreach ($entity_data_array as $file_name => $entity) {
      $submission = reset($entity_array);
      $serializer = \Drupal::service('serializer');
      $xml = $serializer->serialize($entity, 'smartling_xml');
      $file = file_save_data($xml, 'private://' . $file_name);

      $event = $this->file_transport->upload($content, $submission, $target_locales[$file_name]);

      foreach ($entity_array as $submission) {
        $submission->setStatusByEvent($event)->save();

        $this->drupal_wrapper->rulesIvokeEvent('smartling_after_submission_upload_event', array($submission->getEID()));
      }
    }
  }

}
