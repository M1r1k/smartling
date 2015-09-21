<?php

/**
 * @file
 * Contains \Drupal\smartling\SmartlingEntityDataInterface.
 */

namespace Drupal\smartling;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Provides an interface defining a smartling data entity.
 */
interface SmartlingEntityDataInterface extends EntityInterface {

  /**
   * Creates new entity from Content Entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A content entity.
   * @param \Drupal\Core\Language\LanguageInterface $target_language
   *   A language to translate.
   *
   * @return \Drupal\smartling\SmartlingEntityDataInterface
   *   Unsaved entity.
   */
  public static function createFromDrupalEntity(ContentEntityInterface $entity, LanguageInterface $target_language);

  /**
   * Load all entities using some conditions.
   *
   * @param array $conditions
   *
   * @return \Drupal\smartling\SmartlingEntityDataInterface[]
   */
  public static function loadMultipleByConditions(array $conditions);

  /**
   * Load first entity that matches conditions.
   *
   * @param array $conditions
   *
   * @return \Drupal\smartling\SmartlingEntityDataInterface
   */
  public static function loadByConditions(array $conditions);

}
