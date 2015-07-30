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

class ExpertInfoSettingsForm extends ConfigFormBase {

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
    return 'smartling_expert_info_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['log_mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Smartling log'),
      '#default_value' => $this->config('smartling.settings')->get('expert.log_mode'),
      '#description' => t('Log ON dy default.'),
    );

    $form['async_mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Asynchronous mode'),
      '#description' => t('If you uncheck this, the Smartling Connector will attempt to submit content immediately to Smartling servers.'),
      '#default_value' => $this->config('smartling.settings')->get('expert.async_mode'),
    );

    $form['convert_entities_before_translation'] = array(
      '#type' => 'checkbox',
      '#title' => t('Convert entities before translation'),
      '#description' => t('If this is unchecked, then you should convert your content manually from "language-neutral" to default language (usually english) before sending content item for translation.'),
      '#default_value' => $this->config('smartling.settings')->get('expert.convert_entities_before_translation'),
    );

    $form['ui_translations_merge_mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('UI translation mode'),
      '#description' => t('If checked: Translation import mode keeping existing translations and only inserting new strings, strings overwrite happens otherwise.'),
      '#default_value' => $this->config('smartling.settings')->get('expert.ui_translations_merge_mode'),
    );

    $form['custom_regexp_placeholder'] = array(
      '#type' => 'textfield',
      '#title' => t('Custom RegExp for placeholder'),
      '#description' => t('The content that matches this regular expression will be replaced before translation in Smartling dashboard.'),
      '#default_value' => $this->config('smartling.settings')->get('expert.custom_regexp_placeholder'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('smartling.settings')
      ->set('expert.log_mode', $form_state->getValue('log_mode'))
      ->set('expert.async_mode', $form_state->getValue('async_mode'))
      ->set('expert.convert_entities_before_translation', $form_state->getValue('convert_entities_before_translation'))
      ->set('expert.ui_translations_merge_mode', $form_state->getValue('ui_translations_merge_mode'))
      ->set('expert.custom_regexp_placeholder', $form_state->getValue('custom_regexp_placeholder'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
