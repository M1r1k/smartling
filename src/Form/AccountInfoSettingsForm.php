<?php

/**
 * @file
 * Contains \Drupal\smartling\Form\AccountInfoSettingsForm.
 */

namespace Drupal\smartling\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Smartling account settings form.
 */
class AccountInfoSettingsForm extends ConfigFormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a AccountInfoSettingsForm object.
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
      'smartling.settings',
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

    $config = $this->config('smartling.settings');
    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL'),
      '#default_value' => $config->get('account_info.api_url'),
      '#size' => 25,
      '#maxlength' => 255,
      '#required' => FALSE,
      '#description' => $this->t('Set api url. Default: @api_url', ['@api_url' => $config->get('account_info.api_url')]),
    ];

    $form['project_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project Id'),
      '#default_value' => $config->get('account_info.project_id'),
      '#size' => 25,
      '#maxlength' => 25,
      '#required' => TRUE,
    ];

    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#default_value' => '',
      '#description' => $this->t('Current key: @key', ['@key' => $this->hideKey($config->get('account_info.key'))]),
      '#size' => 40,
      '#maxlength' => 40,
      '#required' => FALSE,
    ];

    /* @var \Drupal\Core\Language\LanguageInterface[] $languages */
    $languages = locale_translatable_language_list();
    if (empty($languages)) {
      // Display stubs to pass value to submit function.
      $form['target_locales'] = [
        '#type' => 'item',
        '#title' => $this->t('Target Locales'),
        '#value' => [],
        '#description' => [
          '#type' => 'link',
          '#title' => $this->t('At least two languages must be enabled. Please change language settings.'),
          '#url' => $url,
        ],
      ];
      $form['target_locales_text_keys'] = [
        '#type' => 'value',
        '#value' => [],
      ];
    }
    else {
      foreach ($languages as $langcode => $language) {
        $languages[$langcode] = $language->getName();
      }
      $form['target_locales'] = [
        '#type' => 'checkboxes',
        '#options' => $languages,
        '#title' => $this->t('Target Locales'),
        '#description' => $this->t('In order to get values for these fields, please visit API section of Smartling dashboard: https://dashboard.smartling.com/settings/api.htm'),
        '#default_value' => $config->get('account_info.target_locales'),
        '#prefix' => '<div class="wrap-target-locales">',
        // Attach library only when any language exists.
        '#attached' => [
          'library' => ['smartling/smartling.admin'],
          'drupalSettings' => ['smartling' => ['checkAllId' => ['#edit-target-locales']]],
        ]
      ];

      $total = count($languages);
      $counter = 0;
      $form['target_locales_text_keys'] = [
        '#tree' => TRUE,
      ];
      foreach (array_keys($languages) as $langcode) {
        $counter++;

        $form['target_locales_text_keys'][$langcode] = [
          '#type' => 'textfield',
          '#default_value' => $config->get('account_info.target_locales_text_keys.' . $langcode),
          '#size' => 6,
          '#maxlength' => 10,
          // @todo Fix states that update removed element.
//          '#states' => [
//            'disabled' => [
//              'input#edit-target-locales-' . $langcode => ['checked' => FALSE],
//            ],
//          ],
        ];

        if ($counter == 1) {
          $form['target_locales_text_keys'][$langcode]['#prefix'] = '<div class="wrap-target-locales-text-key">';
        }

        if ($counter == $total) {
          $form['target_locales_text_keys'][$langcode]['#suffix'] = '</div></div>';
        }
      }
    }

    $default_language = $this->languageManager->getDefaultLanguage();
    $form['default_language'] = [
      '#type' => 'item',
      '#title' => $this->t('Site default language: %lang_name', [
        '%lang_name' => $default_language->getName(),
      ]),
      '#description' => [
        '#type' => 'link',
        '#title' => $this->t('Change default language'),
        '#url' => $url,
      ],
    ];

    $form['callback_url_use'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Smartling callback: /smartling/callback/%cron_key'),
      // @todo Add description to display full URL.
      '#default_value' => $config->get('account_info.callback_url_use'),
      '#required' => FALSE,
    ];

    $form['auto_authorize_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto authorize content'),
      '#default_value' => $config->get('account_info.auto_authorize_content'),
      '#required' => FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('smartling.settings.account_info');
    $config
      ->set('api_url', $form_state->getValue('api_url'))
      ->set('project_id', $form_state->getValue('project_id'))
      ->set('callback_url_use', $form_state->getValue('callback_url_use'))
      ->set('auto_authorize_content', $form_state->getValue('auto_authorize_content'))
      ->set('target_locales', $form_state->getValue('target_locales'));

    if ($key = trim($form_state->getValue('key'))) {
      // Do not update existing key if new key missing.
      $config->set('key', $key);
    }

    foreach ($form_state->getValue('target_locales_text_keys') as $lang => $enabled) {
      $config->set('target_locales_text_keys.' . $lang, $enabled);
    }
    $config->save();

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
