<?php
/**
 * @file
 * Contains \Drupal\smartling\Controller\AdminSettingsController.
 */

namespace Drupal\smartling\Controller;
use Drupal\Core\Form\FormInterface;

/**
 * AdminSettingsController.
 */
class AdminSettingsController {

  private function wrapWithFieldset(FormInterface $form, $title) {
    return [
      '#type' => 'details',
      '#group' => 'smartling',
      '#title' => $title,
      '#attributes' => array(
        'class' => array('smartling-' . strtolower(str_replace(' ', '-', $title))),
        'id' => array('smartling-' . strtolower(str_replace(' ', '-', $title))),
      ),
      'children' => $form,
    ];
  }

  /**
   * Generates an example page.
   */
  public function settingsPage() {
    // Add ajax library.
   // drupal_add_library('system', 'drupal.ajax');
    $output['message'] = [
      '#type' => 'markup',
      '#title' => 'Link to submission views',
      '#title_display' => 'invisible',
    //  @ToDo:
    //  '#prefix' => t('After you configure Smartling module you can <a href="@url">start submitting your content</a>.', array('@url' => url('admin/content/smartling-content'))),
    ];

    $settings_forms = [
      'Drupal\smartling\Form\AccountInfoSettingsForm' => 'Account info',
      'Drupal\smartling\Form\ExpertInfoSettingsForm' => 'Expert info',
    ];
    //module_invoke_all('smartling_settings_form_info');

    foreach ($settings_forms as $machine_name => $title) {
      $form = \Drupal::formBuilder()->getForm($machine_name);
      $output[$machine_name . '_details'] = $this->wrapWithFieldset($form, $title);
    }

    return $output;
  }
}