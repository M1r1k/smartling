<?php

/**
 * @file
 * Contains \Drupal\smartling\Plugin\Action\TranslateNode.
 */

namespace Drupal\smartling\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\smartling\SmartlingManager;

/**
 * Translate entity.
 *
 * @Action(
 *   id = "smartling_translate_node_action",
 *   label = @Translation("Translate node to all configured languages"),
 *   type = "node"
 * )
 */
class TranslateNode extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    // @todo add support of sync mode here.
    /** @var \Drupal\smartling\SmartlingManager $smartling_manager */
    $smartling_manager = \Drupal::service('smartling.manager');
    $language_manager = \Drupal::languageManager();
    $smartling_ids = [];
    $config = \Drupal::config('smartling.schema')->get('target_locales');
    foreach ($config as $language_code) {
      $language = $language_manager->getLanguage($language_code);
      foreach ($entities as $entity) {
        $smartling_manager[] = $smartling_manager->getSmartlingEntityFromContentEntity($entity, $language)->id();
      }
    }
    $smartling_manager->addUploadQueueWorker($smartling_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $smartling_manager = \Drupal::service('smartling.manager');
    $smartling_manager->addUploadQueueWorker([$entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    // @todo check smartling permissions.
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->status->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
