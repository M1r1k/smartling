<?php

/**
 * @file
 * Main settings form.
 */

namespace Drupal\smartling\Form;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AccountInfoSettingsForm extends ConfigFormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a CronForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The state key value store.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'smartling.account_info',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smartling_account_info_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $url = Url::fromRoute('entity.configurable_language.collection');

//    $form['account_info'] = array(
//      'actions' => array(
//        '#type' => 'actions',
//      ),
//      '#attached' => array(
//        'js' => array(drupal_get_path('module', 'smartling') . '/js/smartling_check_all.js'),
//      ),
//    );
//    drupal_add_js(array('smartling' => array('checkAllId' => array('#edit-target-locales'))), 'setting');

    $form['account_info']['title'] = [
      '#type' => 'item',
      '#title' => t('Account info'),
    ];

    $form['account_info']['api_url'] = [
      '#type' => 'textfield',
      '#title' => t('API URL'),
      '#default_value' => $this->config('smartling.account_info')->get('api_url'),
      '#size' => 25,
      '#maxlength' => 255,
      '#required' => FALSE,
      '#description' => t('Set api url. Default: @api_url', ['@api_url' => $this->config('smartling.account_info')->get('api_url')]),
    ];

    $form['account_info']['project_id'] = [
      '#type' => 'textfield',
      '#title' => t('Project Id'),
      '#default_value' => $this->config('smartling.account_info')->get('project_id'),
      '#size' => 25,
      '#maxlength' => 25,
      '#required' => TRUE,
    ];

    $form['account_info']['key'] = [
      '#type' => 'textfield',
      '#title' => t('Key'),
      '#default_value' => '',
      '#description' => t('Current key: @key', ['@key' => $this->hideKey($this->config('smartling.account_info')->get('key'))]),
      '#size' => 40,
      '#maxlength' => 40,
      '#required' => FALSE,
    ];

    //@ToDo: replace direct functon call with a wrapper
//    $target_language_options_list = locale_translatable_language_list();
//    if (!empty($target_language_options_list)) {
//      foreach($target_language_options_list as $k => $v) {
//        $target_language_options_list[$k] = $v->getName();
//      }
//      $form['account_info']['target_locales'] = array(
//        '#type' => 'checkboxes',
//        '#options' => $target_language_options_list,
//        '#title' => t('Target Locales'),
//        '#description' => t('In order to get values for these fields, please visit API section of Smartling dashboard: https://dashboard.smartling.com/settings/api.htm'),
//        '#default_value' => $config->get('smartling_target_locales'),//$settings->getTargetLocales(),
//        '#prefix' => '<div class="wrap-target-locales">',
//      );
//
//      $total = count($target_language_options_list);
//      $counter = 0;
//      $locales_convert_array = $config->get('smartling_locales_convert_array');//$settings->getLocalesConvertArray();
//      foreach (array_keys($target_language_options_list) as $langcode) {
//        $counter++;
//
//        $form['account_info']['target_locales_text_key_' . $langcode] = array(
//          '#type' => 'textfield',
//          '#title' => '',
//          '#title_display' => 'invisible',
//          '#default_value' => (isset($locales_convert_array[$langcode]) && ($locales_convert_array[$langcode] != $langcode)) ? $locales_convert_array[$langcode] : '',
//          '#size' => 6,
//          '#maxlength' => 10,
//          '#required' => FALSE,
//          '#states' => array(
//            'disabled' => array(
//              ':input[name="target_locales[' . $langcode . ']"]' => array('checked' => FALSE),
//            ),
//          ),
//        );
//
//        if ($counter == 1) {
//          $form['account_info']['target_locales_text_key_' . $langcode]['#prefix'] = '<div class="wrap-target-locales-text-key">';
//        }
//
//        if ($counter == $total) {
//          $form['account_info']['target_locales_text_key_' . $langcode]['#suffix'] = '</div></div>';
//        }
//      }
//    }
//    else {
      $form['account_info']['target_locales'] = [
        '#type' => 'checkboxes',
        '#options' => array(),
        '#title' => t('Target Locales'),
        '#default_value' => array(),
        // @todo build link directly using router.
        '#description' => \Drupal::l(t('At least two languages must be enabled. Please change language settings.'), $url),
      ];
//    }

    $form['account_info']['default_language'] = [
      '#type' => 'item',
      '#title' => t('Default language'),
    ];

    $form['account_info']['default_language_markup'] = [
      '#markup' => '<p>' . t('Site default language: @lang', array('@lang' => $this->languageManager->getDefaultLanguage()->getName())) . '</p>',
      // @todo build link directly using router.
      '#suffix' => '<p>' . \Drupal::l(t('Change default language'), $url) . '</p>',
    ];

    $form['account_info']['callback_info_title'] = [
      '#type' => 'item',
      '#title' => t('Callback URL'),
    ];

    $form['account_info']['callback_url_use'] = [
      '#type' => 'checkbox',
      '#title' => t('Use Smartling callback: /smartling/callback/%cron_key'),
      '#default_value' => $this->config('smartling.account_info')->get('callback_url_use'),
      '#required' => FALSE,
    ];

    $form['account_info']['auto_authorize_content_title'] = [
      '#type' => 'item',
      '#title' => t('Auto authorize'),
    ];

    $form['account_info']['auto_authorize_content'] = [
      '#type' => 'checkbox',
      '#title' => t('Auto authorize content'),
      '#default_value' => $this->config('smartling.account_info')->get('auto_authorize_content'),
      '#required' => FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('smartling.account_info')
      ->set('api_url', $form_state->getValue('api_url'))
      ->set('project_id', $form_state->getValue('project_id'))
      ->set('key', $form_state->getValue('key'))
      ->set('callback_url_use', $form_state->getValue('callback_url_use'))
      ->set('auto_authorize_content', $form_state->getValue('auto_authorize_content'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Hide last 10 characters in string.
   *
   * @param string $key
   *   Smartling key.
   *
   * @return string
   *   Return smartling key without 10 last characters.
   */
  protected function hideKey($key = '') {
    return substr($key, 0, -10) . str_repeat("*", 10);
  }

  /**
   * Check api key.
   *
   * @param string $key
   *   Api key.
   *
   * @return string
   *   Return checked api key.
   */
  protected function apiKeyCheck($key) {
    return preg_match("/^[a-z0-9]{8}(?:-[a-z0-9]{4}){3}-[a-z0-9]{12}$/", $key);
  }

  /**
   * Check project id.
   *
   * @param string $project_id
   *   Project id.
   *
   * @return string
   *   Return checked project id.
   */
  protected function projectIdCheck($project_id) {
    return preg_match("/^[a-z0-9]{9}$/", $project_id);
  }

}
