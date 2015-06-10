<?php

/**
 * @file
 * Main settings form.
 */

namespace Drupal\smartling\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class AdminAccountInfoSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'smartling.admin',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smartling_admin_account_info_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('smartling.settings');
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

    $form['account_info']['title'] = array(
      '#type' => 'item',
      '#title' => t('Account info'),
    );

    $form['account_info']['api_url'] = array(
      '#type' => 'textfield',
      '#title' => t('API URL'),
      '#default_value' => $config->get('smartling_api_url'),
      '#size' => 25,
      '#maxlength' => 255,
      '#required' => FALSE,
      '#description' => t('Set api url. Default: @api_url', array('@api_url' => $config->get('smartling_api_url'))),
    );

    $form['account_info']['project_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Project Id'),
      '#default_value' => $config->get('smartling_project_id'),
      '#size' => 25,
      '#maxlength' => 25,
      '#required' => TRUE,
    );

    $form['account_info']['smartling_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Key'),
      '#default_value' => '',
      '#description' => t('Current key: @key', array('@key' => $this->hideKey($config->get('smartling_key')))),
      '#size' => 40,
      '#maxlength' => 40,
      '#required' => FALSE,
    );

    //@ToDo: replace direct functon call with a wrapper
    $target_language_options_list = locale_translatable_language_list();
    if (!empty($target_language_options_list)) {
      foreach($target_language_options_list as $k => $v) {
        $target_language_options_list[$k] = $v->getName();
      }
      $form['account_info']['target_locales'] = array(
        '#type' => 'checkboxes',
        '#options' => $target_language_options_list,
        '#title' => t('Target Locales'),
        '#description' => t('In order to get values for these fields, please visit API section of Smartling dashboard: https://dashboard.smartling.com/settings/api.htm'),
        '#default_value' => $config->get('smartling_target_locales'),//$settings->getTargetLocales(),
        '#prefix' => '<div class="wrap-target-locales">',
      );

      $total = count($target_language_options_list);
      $counter = 0;
      $locales_convert_array = $config->get('smartling_locales_convert_array');//$settings->getLocalesConvertArray();
      foreach (array_keys($target_language_options_list) as $langcode) {
        $counter++;

        $form['account_info']['target_locales_text_key_' . $langcode] = array(
          '#type' => 'textfield',
          '#title' => '',
          '#title_display' => 'invisible',
          '#default_value' => (isset($locales_convert_array[$langcode]) && ($locales_convert_array[$langcode] != $langcode)) ? $locales_convert_array[$langcode] : '',
          '#size' => 6,
          '#maxlength' => 10,
          '#required' => FALSE,
          '#states' => array(
            'disabled' => array(
              ':input[name="target_locales[' . $langcode . ']"]' => array('checked' => FALSE),
            ),
          ),
        );

        if ($counter == 1) {
          $form['account_info']['target_locales_text_key_' . $langcode]['#prefix'] = '<div class="wrap-target-locales-text-key">';
        }

        if ($counter == $total) {
          $form['account_info']['target_locales_text_key_' . $langcode]['#suffix'] = '</div></div>';
        }
      }
    }
    else {
      $form['account_info']['target_locales'] = array(
        '#type' => 'checkboxes',
        '#options' => array(),
        '#title' => t('Target Locales'),
        '#default_value' => array(),
        '#description' => \Drupal::l(t('At least two languages must be enabled. Please change language settings.'), $url),
      );
    }

    $form['account_info']['default_language'] = array(
      '#type' => 'item',
      '#title' => t('Default language'),
    );

    $form['account_info']['default_language_markup'] = array(
      '#markup' => '<p>' . t('Site default language: @lang', array('@lang' => ''/* language_default()->name*/)) . '</p>',
      '#suffix' => '<p>' . \Drupal::l(t('Change default language'), $url) . '</p>',
    );

    $form['account_info']['callback_info_title'] = array(
      '#type' => 'item',
      '#title' => t('Callback URL'),
    );

    $form['account_info']['callback_url_use'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Smartling callback: /smartling/callback/%cron_key'),
      '#default_value' => $config->get('smartling_callback_url_use'),//$settings->getCallbackUrlUse(),
      '#required' => FALSE,
    );

    $form['account_info']['auto_authorize_content_title'] = array(
      '#type' => 'item',
      '#title' => t('Auto authorize'),
    );

    $form['account_info']['auto_authorize_content'] = array(
      '#type' => 'checkbox',
      '#title' => t('Auto authorize content'),
      '#default_value' => $config->get('smartling_auto_authorize_content'),//$settings->getAutoAuthorizeContent(),
      '#required' => FALSE,
    );

    //$form['#validate'][] = 'smartling_admin_account_info_settings_form_validate';
    //$form['#submit'][] = 'smartling_admin_account_info_settings_form_submit';



    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    Drupal::service('config.factory')
      ->getEditable('smartling.settings')
      ->set('smartling_api_url', $form_state->getValue('api_url'))
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