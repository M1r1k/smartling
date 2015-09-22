<?php

/**
 * @file
 * Contains \Drupal\smartling\ApiWrapper\SmartlingApiWrapper.
 */

namespace Drupal\smartling\ApiWrapper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\smartling\SmartlingEntityDataInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Smartling\SmartlingApi;

/**
 * Class SmartlingApiWrapper.
 */
class SmartlingApiWrapper implements ApiWrapperInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * @var \Smartling\SmartlingApi
   */
  protected $api;

  /**
   * This function converts Drupal locale to Smartling locale.
   *
   * @param string $locale
   *   Locale string in some format: 'en' or 'en-US'.
   *
   * @return string|null
   *   Return locale or NULL.
   */
  protected function convertLocaleDrupalToSmartling($locale) {
    return $this->config->get('account_info.target_locales.' . $locale);
  }

  /**
   * Initialize.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configs
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   * @param \GuzzleHttp\ClientInterface $http_client
   */
  public function __construct(ConfigFactoryInterface $configs, LoggerChannelInterface $logger, ClientInterface $http_client) {
    $this->settingsHandler = $configs->get('smartling_settings');
    $this->logger = $logger;

    // 
    $this->setApi(new SmartlingApi($configs->get('account_info.api_url'), $configs->get('account_info.key'), $configs->get('account_info.project_id'), $http_client, SmartlingApi::PRODUCTION_MODE));
  }

  /**
   * {@inheritdoc}
   */
  public function setApi(SmartlingApi $api) {
    $this->api = $api;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocaleList() {
    $data = $this->api->getLocaleList();
    $result = array();
    foreach ($data['locales'] as $locale) {
      $result[$locale->locale] = "{$locale->name} ({$locale->translated})";
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function downloadFile(SmartlingEntityDataInterface $smartling_entity) {
    $smartling_entity_type = $smartling_entity->entity_type;
    $d_locale = $smartling_entity->target_language;
    $file_name_unic = $smartling_entity->file_name;

    $retrieval_type = $this->config->get('retrieval_type');
    $download_param = array('retrievalType' => $retrieval_type);

    $this->logger->info("Smartling queue start download '@file_name' file and update fields for @entity_type id - @rid, locale - @locale.",
      array('@file_name' => $file_name_unic, '@entity_type' => $smartling_entity_type, '@rid' => $smartling_entity->rid, '@locale' => $smartling_entity->target_language));

    $s_locale = $this->convertLocaleDrupalToSmartling($d_locale);
    // @todo catch exception.
    $file_data = $this->api->downloadFile($file_name_unic, $s_locale, $download_param);

    return $file_data;
  }


  /**
   * {@inheritdoc}
   */
  public function getStatus(SmartlingEntityDataInterface $smartling_entity) {
    $file_name = $smartling_entity->get('file_name')->value;

    $s_locale = $this->convertLocaleDrupalToSmartling($smartling_entity->get('target_language')->value);
    // @todo handle exceptions.
    $status = $this->api->getStatus($file_name, $s_locale);

    $this->logger->info('Smartling checks status for @entity_type id - @rid (@d_locale). approvedString = @as, completedString = @cs',
      array('@entity_type' => $smartling_entity->get('entity_type')->value, '@rid' => $smartling_entity->get('rid')->value, '@d_locale' => $smartling_entity->get('target_language')->value,
            '@as' => $status['approvedStringCount'], '@cs' => $status['completedStringCount']));

    // If true, file translated.
    $approved = $status['approvedStringCount'];
    $completed = $status['completedStringCount'];
    $progress = ($approved > 0) ? (int) (($completed / $approved) * 100) : 0;
    $smartling_entity->set('download', 0);
    $smartling_entity->set('progress', $progress);
    $smartling_entity->set('status', SMARTLING_STATUS_IN_TRANSLATE);

    return array(
      'entity_data' => $smartling_entity,
      'response_data' => $status,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function testConnection(array $locales) {
    $result = array();

    foreach ($locales as $key => $locale) {
      if ($locale !== 0 && $locale == $key) {
        $s_locale = $this->convertLocaleDrupalToSmartling($locale);
        // @todo handle Smarlting exceptions.
        $list = $this->api->getList($s_locale, ['limit' => 1]);

        $result[$s_locale] = TRUE;
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  // TODO : Replace $file_type with enum class
  public function uploadFile($file_path, $file_name, $file_type, array $locales) {
    $locales_to_approve = $upload_params = [];
    foreach ($locales as $locale) {
      $locales_to_approve[] = $this->convertLocaleDrupalToSmartling($locale);
    }

    if ($this->config->get('account_info.auto_authorize_content')) {
      $upload_params['approved'] = TRUE;
    }
    if ($this->config->get('account_info.callback_url_use')) {
      $upload_params['callbackUrl'] = $this->config->get('account_info.callback_url');
    }

    $uploaded = $this->api->uploadFile($file_path, $file_name, $file_type, $upload_params);

    $this->logger->info('Smartling uploaded @file_name for locales: @locales',
      array('@file_name' => $file_name, '@locales' => implode('; ', $locales), 'entity_link' => l(t('View file'), $file_path)));

    return SMARTLING_STATUS_EVENT_UPLOAD_TO_SERVICE;
    // @todo handle Smartling Exceptions.
    //return SMARTLING_STATUS_EVENT_FAILED_UPLOAD;
  }

  /**
   * Uploads context file to Smartling and writes some logs
   *
   * @param array $data
   *
   * @return int
   *
   * @todo convert this method as well.
   */
  public function uploadContext($data) {
    $data['action'] = 'upload';
    $upload_result = $this->api->uploadContext($data);

    if ($this->api->getCodeStatus() !== 'SUCCESS') {
      $this->logger->error('Smartling failed to upload context for module @angular_module with message: @message', array('@angular_module' => $data['url'], '@message' => $upload_result));
      return -1;
    }

    $upload_result = json_decode($upload_result);
    $requestId = $upload_result->response->data->requestId;

    $data = array(
      'requestId' => $requestId,
      'action' => 'getStats'
    );

    $upload_result = $this->api->getContextStats($data);

    if ($this->api->getCodeStatus() !== 'SUCCESS') {
      $this->logger->error('Smartling uploaded the context, but failed to get context statistics for request: @requestId  with message: @message', array('@requestId' => $requestId, '@message' => $upload_result));
      return -1;
    }

    $upload_result = json_decode($upload_result);
    $updatedStringsCount = $upload_result->response->data->updatedStringsCount;

    return $updatedStringsCount;
  }
}
