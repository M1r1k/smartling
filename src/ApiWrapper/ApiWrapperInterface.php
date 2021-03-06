<?php

/**
 * @file
 * Contains \Drupal\smartling\ApiWrapperInterface.
 */

namespace Drupal\smartling\ApiWrapper;
use Drupal\smartling\SmartlingEntityDataInterface;
use Smartling\SmartlingApi;

/**
 * Provides an interface defining a smartling API wrapper.
 */
interface ApiWrapperInterface {

  /**
   * Set Smartling API instance.
   *
   * @param \Smartling\SmartlingApi $api
   *   Smartling API object from Smartling PHP SDK.
   */
  public function setApi(SmartlingApi $api);

  /**
   * Download file from service.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_entity
   *   Smartling transaction entity.
   *
   * @return \DOMDocument|boolean
   *   Return xml dom from downloaded file.
   */
  public function downloadFile(SmartlingEntityDataInterface $smartling_entity);

  /**
   * Get status of given entity's translation progress.
   *
   * @param \Drupal\smartling\SmartlingEntityDataInterface $smartling_entity
   *   Smartling transaction entity.
   *
   * @return array|null
   *   Return status.
   */
  public function getStatus(SmartlingEntityDataInterface $smartling_entity);

  /**
   * Test Smartling API instance init and connection to Smartling server.
   *
   * @param array $locales
   *   List of locales in Drupal format.
   *
   * @return array
   *   If connections were successful for each locale.
   */
  public function testConnection(array $locales);

  /**
   * Upload local file to Smartling for translation.
   *
   * @param string $file_path
   *   Real path to file.
   * @param string $file_name_unic
   *   Unified file name.
   * @param string $file_type
   *   File type. Use only 2 values 'xml' or 'getext'
   * @param array $locales
   *   List of locales in Drupal format.
   *
   * @return string
   *   SMARTLING_STATUS_EVENT_UPLOAD_TO_SERVICE | SMARTLING_STATUS_EVENT_FAILED_UPLOAD
   */
  public function uploadFile($file_path, $file_name_unic, $file_type, array $locales);

  /**
   * Gets list of locales for project.
   *
   * @return array
   *   An array of locales.
   */
  public function getLocaleList();

}
