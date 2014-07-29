<?php

/**
 * @file
 * Smartling settings handler.
 */

namespace Drupal\smartling\Settings;

/**
 * Class SmartlingSettingsHandler.
 */
class SmartlingSettingsHandler {

  protected $apiUrl;
  protected $projectId;
  protected $key;
  protected $retrievalType;
  protected $targetLocales;
  protected $localesConvertArray;
  protected $callbackUrlUse;
  protected $callbackUrl;
  protected $autoAuthorizeContent;
  protected $logMode;
  protected $nodeFieldsSettings;
  protected $commentFieldsSettings;
  protected $taxonomyTermFieldsSettings;
  protected $userFieldsSettings;

  /**
   * Initialize.
   */
  public function __construct() {
    $this->apiUrl = $this->variableGet('smartling_api_url', SMARTLING_DEFAULT_API_URL);
    $this->callbackUrlUse = $this->variableGet('smartling_callback_url_use', TRUE);
    $this->callbackUrl = $this->getBaseUrl() . '/smartling/callback/' . $this->variableGet('cron_key', 'drupal');
    $this->autoAuthorizeContent = $this->variableGet('smartling_auto_authorize_content', TRUE);
    $this->logMode = $this->variableGet('smartling_log', 1);
    $this->projectId = $this->variableGet('smartling_project_id', '');
    $this->key = $this->variableGet('smartling_key', '');
    $this->retrievalType = $this->variableGet('smartling_retrieval_type', 'published');

    $this->targetLocales = $this->variableGet('smartling_target_locales', array());
    $this->localesConvertArray = $this->variableGet('smartling_locales_convert_array', array());

    $this->nodeFieldsSettings = $this->variableGet('smartling_node_fields_settings', array());
    $this->commentFieldsSettings = $this->variableGet('smartling_comment_fields_settings', array());
    $this->taxonomyTermFieldsSettings = $this->variableGet('smartling_taxonomy_term_fields_settings', array());
    $this->userFieldsSettings = $this->variableGet('smartling_user_fields_settings', array());
  }

  /**
   * Wrapper for variable_get() function.
   *
   * @param string $variable_name
   *   Variable name.
   * @param mixed $default_value
   *   Variable default value.
   *
   * @return mixed
   *   Return variable value.
   */
  public function variableGet($variable_name, $default_value = NULL) {
    return variable_get($variable_name, $default_value);
  }

  /**
   * Wrapper for variable_set() function.
   *
   * @param string $variable_name
   *   Variable name.
   * @param mixed $value
   *   Variable value.
   */
  protected function variableSet($variable_name, $value) {
    variable_set($variable_name, $value);
  }

  /**
   * Wrapper for variable_del() function.
   *
   * @param string $variable_name
   *   Variable name.
   */
  protected function variableDel($variable_name) {
    variable_del($variable_name);
  }

  /**
   * Getter for global base_url variable.
   *
   * @return string
   *   BaseUrl.
   */
  public function getBaseUrl() {
    global $base_url;
    return $base_url;
  }
  /**
   * Get property name by entity type.
   *
   * @param string $entity_type
   *   Entity type.
   *
   * @return string
   *   Return property name.
   */
  public static function getPropertyName($entity_type) {
    if ($entity_type == 'taxonomy_term') {
      $property_name = 'taxonomyTermFieldsSettings';
    }
    else {
      $property_name = $entity_type . 'FieldsSettings';
    }
    return $property_name;
  }

  /**
   * Get method name by entity type.
   *
   * @param string $entity_type
   *   Entity type.
   *
   * @return string
   *   Return method name.
   */
  public static function getMethodName($entity_type) {
    if ($entity_type == 'taxonomy_term') {
      $method_name = 'taxonomyTermGetFieldsSettings';
    }
    else {
      $method_name = $entity_type . 'GetFieldsSettings';
    }
    return $method_name;
  }

  /**
   * Get ...by_bundle method name by entity type.
   *
   * @param string $entity_type
   *   Entity type.
   *
   * @return string
   *   Return method name.
   */
  public static function getByBundleMethodName($entity_type) {
    if ($entity_type == 'taxonomy_term') {
      $method_name = 'taxonomyTermGetFieldsSettingsByBundle';
    }
    else {
      $method_name = $entity_type . 'GetFieldsSettingsByBundle';
    }
    return $method_name;
  }

  /**
   * Set smartling fields settings for node entity.
   *
   * @param array $node_fields_settings
   *   Smartling fields settings for node entity.
   */
  public function nodeSetFieldsSettings(array $node_fields_settings) {
    if (!empty($node_fields_settings)) {
      $this->nodeFieldsSettings = $node_fields_settings;
      $this->variableSet('smartling_node_fields_settings', $node_fields_settings);
    }
    else {
      $this->nodeFieldsSettings = array();
      $this->variableDel('smartling_node_fields_settings');
    }
  }

  /**
   * Get smartling fields settings array for node.
   *
   * @return array
   *   Return smartling fields settings array for node entity.
   */
  public function nodeGetFieldsSettings() {
    return $this->nodeFieldsSettings;
  }


  /**
   * Get smartling fields settings array for node by bundle.
   *
   * @param string $bundle
   *   Entity bundle.
   *
   * @return array
   *   Return smartling fields settings array.
   */
  public function nodeGetFieldsSettingsByBundle($bundle) {
    return (isset($this->nodeFieldsSettings[$bundle])) ? $this->nodeFieldsSettings[$bundle] : array();
  }

  /**
   * Set smartling fields settings for comment entity.
   *
   * @param array $comment_fields_settings
   *   Smartling fields settings for comment entity.
   */
  public function commentSetFieldsSettings(array $comment_fields_settings) {
    if (!empty($comment_fields_settings)) {
      $this->commentFieldsSettings = $comment_fields_settings;
      $this->variableSet('smartling_comment_fields_settings', $comment_fields_settings);
    }
    else {
      $this->commentFieldsSettings = array();
      $this->variableDel('smartling_comment_fields_settings');
    }
  }

  /**
   * Get smartling fields settings array for comment.
   *
   * @return array
   *   Return smartling fields settings array for comment entity.
   */
  public function commentGetFieldsSettings() {
    return $this->commentFieldsSettings;
  }

  /**
   * Get smartling fields settings array for comment by bundle.
   *
   * @param string $bundle
   *   Entity bundle.
   *
   * @return array
   *   Return smartling fields settings array.
   */
  public function commentGetFieldsSettingsByBundle($bundle) {
    return (isset($this->commentFieldsSettings[$bundle])) ? $this->commentFieldsSettings[$bundle] : array();
  }

  /**
   * Set smartling fields settings for taxonomy_term entity.
   *
   * @param array $taxonomy_term_fields_settings
   *   Smartling fields settings for taxonomy_term entity.
   */
  public function taxonomyTermSetFieldsSettings(array $taxonomy_term_fields_settings) {
    if (!empty($taxonomy_term_fields_settings)) {
      $this->taxonomyTermFieldsSettings = $taxonomy_term_fields_settings;
      $this->variableSet('smartling_taxonomy_term_fields_settings', $taxonomy_term_fields_settings);
    }
    else {
      $this->taxonomyTermFieldsSettings = array();
      $this->variableDel('smartling_taxonomy_term_fields_settings');
    }
  }

  /**
   * Get smartling fields settings array for taxonomy_term.
   *
   * @return array
   *   Return smartling fields settings array for taxonomy_term entity.
   */
  public function taxonomyTermGetFieldsSettings() {
    return $this->taxonomyTermFieldsSettings;
  }

  /**
   * Get smartling fields settings array for taxonomy_term by bundle.
   *
   * @param string $bundle
   *   Entity bundle.
   *
   * @return array
   *   Return smartling fields settings array.
   */
  public function taxonomyTermGetFieldsSettingsByBundle($bundle) {
    return (isset($this->taxonomyTermFieldsSettings[$bundle])) ? $this->taxonomyTermFieldsSettings[$bundle] : array();
  }

  /**
   * Set smartling fields settings for user entity.
   *
   * @param array $user_fields_settings
   *   Smartling fields settings for user entity.
   */
  public function userSetFieldsSettings(array $user_fields_settings) {
    if (!empty($user_fields_settings)) {
      $this->userFieldsSettings = $user_fields_settings;
      $this->variableSet('smartling_user_fields_settings', $user_fields_settings);
    }
    else {
      $this->userFieldsSettings = array();
      $this->variableDel('smartling_user_fields_settings');
    }
  }

  /**
   * Get smartling fields settings array for user.
   *
   * @return array
   *   Return smartling fields settings array for user entity.
   */
  public function userGetFieldsSettings() {
    return $this->userFieldsSettings;
  }

  /**
   * Get smartling fields settings array for user by bundle.
   *
   * @param string $bundle
   *   Entity bundle.
   *
   * @return array
   *   Return smartling fields settings array.
   */
  public function userGetFieldsSettingsByBundle($bundle) {
    return (isset($this->userFieldsSettings[$bundle])) ? $this->userFieldsSettings[$bundle] : array();
  }

  /**
   * Add multiple fields to smartling settings.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   * @param array $field_names
   *   Field names.
   */
  public function addMultipleFieldsToSettings($entity_type, $bundle, array $field_names = array()) {
    $name_settings = $this->getPropertyName($entity_type);
    foreach ($field_names as $field_name) {
      $this->{$name_settings}[$bundle][$field_name] = $field_name;
    }
    $this->variableSet('smartling_' . $entity_type . '_fields_settings', $this->{$name_settings});
  }

  /**
   * Add single field to smartling settings.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   * @param string $field_name
   *   Field name.
   */
  public function addSingleFieldToSettings($entity_type, $bundle, $field_name) {
    $this->addMultipleFieldsToSettings($entity_type, $bundle, array($field_name));
  }

  /**
   * Delete multiple fields from smartling settings.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   * @param array $field_names
   *   Field names.
   */
  public function deleteMultipleFieldsFromSettings($entity_type, $bundle, array $field_names = array()) {
    $name_settings = $this->getPropertyName($entity_type);
    foreach ($field_names as $field_name) {
      if (isset($this->{$name_settings}[$bundle][$field_name])) {
        unset($this->{$name_settings}[$bundle][$field_name]);

        if (count($this->{$name_settings}[$bundle]) == 0) {
          unset($this->{$name_settings}[$bundle]);
        }
      }
    }
    if ($this->{$name_settings} == array()) {
      $this->variableDel('smartling_' . $entity_type . '_fields_settings');
    }
    else {
      $this->variableSet('smartling_' . $entity_type . '_fields_settings', $this->{$name_settings});
    }
  }

  /**
   * Delete single field from smartling settings.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   * @param string $field_name
   *   Field name.
   */
  public function deleteSingleFieldFromSettings($entity_type, $bundle, $field_name) {
    $this->deleteMultipleFieldsFromSettings($entity_type, $bundle, array($field_name));
  }

  /**
   * Delete multiple bundles from smartling settings.
   *
   * @param string $entity_type
   *   Entity type.
   * @param array $bundles
   *   Entity bundles.
   */
  public function deleteMultipleBundleFromSettings($entity_type, array $bundles = array()) {
    $name_settings = $this->getPropertyName($entity_type);
    foreach ($bundles as $bundle) {
      if (isset($this->{$name_settings}[$bundle])) {
        unset($this->{$name_settings}[$bundle]);
      }
    }
    if ($this->{$name_settings} == array()) {
      $this->variableDel('smartling_' . $entity_type . '_fields_settings');
    }
    else {
      $this->variableSet('smartling_' . $entity_type . '_fields_settings', $this->{$name_settings});
    }
  }

  /**
   * Delete single bundle from smartling settings.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   */
  public function deleteSingleBundleFromSettings($entity_type, $bundle) {
    $this->deleteMultipleBundleFromSettings($entity_type, array($bundle));
  }

  /**
   * Get smartling API URL.
   *
   * @return string
   *   Return smartling API URL.
   */
  public function getApiUrl() {
    return $this->apiUrl;
  }

  /**
   * Set smartling API URL.
   *
   * @param string $api_url
   *   API url - https://capi.smartling.com/v1 by default.
   */
  public function setApiUrl($api_url) {
    if (empty($api_url)) {
      $api_url = SMARTLING_DEFAULT_API_URL;
    }
    $this->apiUrl = check_plain((string) $api_url);
    $this->variableSet('smartling_api_url', $this->apiUrl);
  }

  /**
   * Get callback url use.
   *
   * @return bool
   *   Return callback url use mode.
   */
  public function getCallbackUrlUse() {
    return $this->callbackUrlUse;
  }

  /**
   * Set callback url use.
   *
   * @param bool $use
   *   TRUE by default.
   */
  public function setCallbackUrlUse($use = TRUE) {
    $this->callbackUrlUse = (bool) $use;
    $this->variableSet('smartling_callback_url_use', $this->callbackUrlUse);
  }

  /**
   * Get auto authorize content.
   *
   * @return bool
   *   Return auto authorize content mode.
   */
  public function getAutoAuthorizeContent() {
    return $this->autoAuthorizeContent;
  }

  /**
   * Set auto authorize content.
   *
   * @param bool $auto
   *   TRUE by default.
   */
  public function setAutoAuthorizeContent($auto = TRUE) {
    $this->autoAuthorizeContent = (bool) $auto;
    $this->variableSet('smartling_auto_authorize_content', $this->autoAuthorizeContent);
  }

  /**
   * Get smartling log mode.
   *
   * @return bool
   *   Return smarling log mode.
   */
  public function getLogMode() {
    return $this->logMode;
  }

  /**
   * Set smartling log mode.
   *
   * @param int $log_mode
   *   1 if log mode ON. 1 by default.
   */
  public function setLogMode($log_mode = 1) {
    $this->logMode = $log_mode;
    $this->variableSet('smartling_log', $this->logMode);
  }

  /**
   * Get log mode options.
   *
   * @return array
   *   Return log mode options.
   */
  public function getLogModeOptions() {
    return array(0 => 'OFF', 1 => 'ON');
  }

  /**
   * Get project id.
   *
   * @return string
   *   Return project id.
   */
  public function getProjectId() {
    return $this->projectId;
  }

  /**
   * Set project id.
   *
   * @param string $project_id
   *   Smartling project id.
   */
  public function setProjectId($project_id) {
    if (empty($project_id)) {
      $this->projectId = NULL;
      $this->variableDel('smartling_project_id');
    }
    else {
      $this->projectId = check_plain((string) $project_id);
      $this->variableSet('smartling_project_id', $this->projectId);
    }
  }

  /**
   * Get smartling account key.
   *
   * @return string
   *   Return smartling account key.
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * Set smartling account key.
   *
   * @param string $key
   *   Smartling account key.
   */
  public function setKey($key) {
    if (!empty($key)) {
      $this->key = check_plain((string) $key);
      $this->variableSet('smartling_key', $this->key);
    }
  }

  /**
   * Get active retrieval type.
   *
   * @return string
   *   Return smartling active retrieval type.
   */
  public function getRetrievalType() {
    return $this->retrievalType;
  }

  /**
   * Set retrieval type.
   *
   * @param string $retrieval_type
   *   Retrieval type.
   */
  public function setRetrievalType($retrieval_type) {
    $this->retrievalType = $retrieval_type;
    $this->variableSet('smartling_retrieval_type', $retrieval_type);
  }

  /**
   * Get retrieval type options array.
   *
   * @return array
   *   Return retrieval type options array.
   */
  public function getRetrievalTypeOptions() {
    return array(
      'pseudo' => 'pseudo',
      'published' => 'published',
      'pending' => 'pending',
    );
  }

  /**
   * Get target language options list.
   *
   * @return array
   *   Return target language options list.
   */
  public function getTargetLanguageOptionsList() {
    $target_language_options_list = array();
    $languages = language_list();
    $default_language = language_default();
    unset($languages[$default_language->language]);

    foreach ($languages as $langcode => $language) {
      if ($language->enabled != '0') {
        $target_language_options_list[$langcode] = check_plain($language->name);
      }
    }
    return $target_language_options_list;
  }

  /**
   * Get target locales array.
   *
   * @return array
   *   Return target locales array.
   */
  public function getTargetLocales() {
    return $this->targetLocales;
  }

  /**
   * Set target locales array.
   *
   * @param array $target_locales
   *   Target locales array.
   */
  public function setTargetLocales(array $target_locales) {
    $this->targetLocales = $target_locales;
    $this->variableSet('smartling_target_locales', $target_locales);
  }

  /**
   * Make and set target locales.
   *
   * Array from $form_state['values']['target_locales'].
   *
   * @param array $target_locales
   *   Target locales array.
   */
  public function makeTargetLocales(array $target_locales) {
    foreach ($target_locales as $key => $lang) {
      // Must be === 0.
      if ($lang === 0) {
        unset($target_locales[$key]);
      }
    }
    if (!empty($target_locales)) {
      $this->targetLocales = $target_locales;
      $this->variableSet('smartling_target_locales', $target_locales);
    }
  }

  /**
   * Get locales convert array.
   *
   * @return array
   *   Return locales convert array.
   */
  public function getLocalesConvertArray() {
    return $this->localesConvertArray;
  }

  /**
   * Set locales convert array.
   *
   * @param array $locales_convert_array
   *   Locales convert array.
   */
  public function setLocalesConvertArray(array $locales_convert_array) {
    $this->localesConvertArray = $locales_convert_array;
    $this->variableSet('smartling_locales_convert_array', $locales_convert_array);
  }

  /**
   * Make locales convert array.
   *
   * @param array $values
   *   Drupal form values array.
   */
  public function makeLocalesConvertArray(array $values) {
    $locales_convert_array = $values['target_locales'];
    foreach ($values['target_locales'] as $key => $lang) {
      // Must be === 0 .
      if ($lang === 0) {
        unset($locales_convert_array[$key]);
      }
      else {
        if (!empty($values['target_locales_text_key_' . $key])) {
          $locales_convert_array[$key] = check_plain($values['target_locales_text_key_' . $key]);
        }
      }
    }
    $this->localesConvertArray = $locales_convert_array;
    $this->variableSet('smartling_locales_convert_array', $locales_convert_array);
  }

  /**
   * Get Smartling dir path.
   *
   * @param string $file_name
   *   File name.
   *
   * @return string
   *   Return smartling dir path.
   */
  public function getDir($file_name = '') {
    $smartling_dir = ($this->variableGet('file_private_path', FALSE)) ? ($this->variableGet('file_private_path') . '/smartling') : ($this->variableGet('file_public_path', conf_path() . '/files') . '/smartling');
    $smartling_dir .= (empty($file_name)) ? '' : '/' . $file_name;
    return (string) $smartling_dir;
  }

  /**
   * Set Smartling callback url.
   *
   * @param string $url
   *   Callback url.
   */
  public function setCallbackUrl($url) {
    $this->callbackUrl = $url;
  }

  /**
   * Get Smartling callback url.
   *
   * @return string
   *   Return smartling callback url.
   */
  public function getCallbackUrl() {
    return $this->callbackUrl;
  }
}
