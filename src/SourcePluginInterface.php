<?php

/**
 * @file
 * Contains \Drupal\smartling\SourcePluginInterface.
 */

namespace Drupal\smartling;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for source plugin controllers.
 */
interface SourcePluginInterface extends PluginInspectionInterface {

  /**
   * Returns an array with the data structured for translation.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   The job item entity.
   *
   * @see JobItem::getData()
   */
  public function getData(SmartlingEntityDataInterface $smartling_item);

  /**
   * Saves a translation.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   The smartling item entity.
   *
   * @return boolean
   *   TRUE if the translation was saved successfully, FALSE otherwise.
   */
  public function saveTranslation(SmartlingEntityDataInterface $smartling_item);

  /**
   * Return a title for this job item.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   The smartling item entity.
   */
  public function getLabel(SmartlingEntityDataInterface $smartling_item);

  /**
   * Returns the Uri for this job item.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   The smartling item entity.
   *
   * @return \Drupal\Core\Url|null
   *   The URL object for the source object.
   */
  public function getUrl(SmartlingEntityDataInterface $smartling_item);

  /**
   * Returns an array of translatable source item types.
   */
  public function getItemTypes();

  /**
   * Returns the label of a source item type.
   *
   * @param $type
   *   The identifier of a source item type.
   */
  public function getItemTypeLabel($type);

  /**
   * Returns the type of a job item.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   The smartling item.
   *
   * @return string
   *   A type that describes the job item.
   */
  public function getType(SmartlingEntityDataInterface $smartling_item);

  /**
   * Gets language code of the job item source.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   The smartling item.
   *
   * @return string
   *   Language code.
   */
  public function getSourceLangCode(SmartlingEntityDataInterface $smartling_item);

  /**
   * Gets existing translation language codes of the job item source.
   *
   * Returns language codes that can be used as the source language for a
   * translation job.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_item
   *   The smartling item.
   *
   * @return array
   *   Array of language codes.
   */
  public function getExistingLangCodes(SmartlingEntityDataInterface $smartling_item);

}
