<?php

/**
 * @file
 * Contains \Drupal\node\Plugin\Action\UnpublishByKeywordNode.
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
    /** @var SmartlingManager $smartling_manager */
    $smartling_manager = \Drupal::service('smartling.manager');
    $smartling_ids = [];
    foreach (\Drupal::config('smartling.schema')->get('target_locales') as $language_code) {
      $language = \Drupal::languageManager()->getLanguage($language_code);
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
