<?php

/**
 * @file
 * Contains \Drupal\smartling\Controller\AdminSettingsController.
 */

namespace Drupal\smartling\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Builds the general settings page.
 */
class AdminSettingsController extends ControllerBase {

  /**
   * Generates an example page.
   */
  public function settings() {
    // Add ajax library.
    // drupal_add_library('system', 'drupal.ajax');
    // @todo Move to hook_help() implementation.
    $build['message'] = [
      '#type' => 'markup',
      '#title' => 'Link to submission views',
      '#title_display' => 'invisible',
    //  '#prefix' => t('After you configure Smartling module you can <a href="@url">start submitting your content</a>.', array('@url' => url('admin/content/smartling-content'))),
    ];

    // @todo Implement vertical tabs, they for forms building only
    $build['smartling_settings'] = [
      '#type' => 'vertical_tabs',
      '#parents' => ['smartling_settings'],
      'group' => ['#groups' => ['smartling_settings' => []]],
      '#attached' => [
        'library' => ['smartling/smartling.admin'],
      ],
    ];

    $settings_forms = [
      'Drupal\smartling\Form\AccountInfoSettingsForm' => 'Account info',
      'Drupal\smartling\Form\ExpertInfoSettingsForm' => 'Expert info',
    ];
    //module_invoke_all('smartling_settings_form_info');

    foreach ($settings_forms as $machine_name => $title) {
      $form = \Drupal::formBuilder()->getForm($machine_name);
      $form_id = str_replace('-', '_', $form['#id']);
      $title_lower = strtolower(str_replace(' ', '-', $title));
      $build[$form_id . '_details'] = [
        '#type' => 'details',
        '#group' => 'smartling_settings',
        '#title' => $title,
        '#attributes' => [
          'class' => ['smartling-' . $title_lower],
          // @todo Get rid of IDs from JS.
          'id' => ['smartling-' . $title_lower],
        ],
      ];
      $build[$form_id . '_details']['form'] = $form;
      //$build[$form_id . '_details']['form'] = ['#type' => 'markup', '#markup' => 'Form'];
      $build[$form_id . '_details']['form']['#group'] = $form_id . '_details';
    }

    return $build;
  }

}
