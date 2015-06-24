<?php
/**
 * @file
 * Contains \Drupal\smartling\Controller\AdminSettingsController.
 */

namespace Drupal\smartling\Controller;

/**
 * AdminSettingsController.
 */
class AdminSettingsController {

  private function wrapInFieldset(array $form, $title) {
    return array(
      '#type' => 'details',//'fieldset',
      '#group' => 'smartling',
      '#title' => $title,
      '#attributes' => array(
        'class' => array('smartling-' . strtolower(str_replace(' ', '-', $title))),
        'id' => array('smartling-' . strtolower(str_replace(' ', '-', $title))),
      ),
      'children' => $form,
    );
  }

  /**
   * Generates an example page.
   */
  public function settingsPage() {
    // Add ajax library.
   // drupal_add_library('system', 'drupal.ajax');
    $output['message'] = array(
      '#type' => 'markup',
      '#title' => 'Link to submission views',
      '#title_display' => 'invisible',
    //  @ToDo:
    //  '#prefix' => t('After you configure Smartling module you can <a href="@url">start submitting your content</a>.', array('@url' => url('admin/content/smartling-content'))),
    );

    $output['smartling'] = array(
//      '#type' => 'vertical_tabs',
//      '#title' => '123'
//      '#attached' => array(
//        'js' => array(drupal_get_path('module', 'smartling') . '/js/smartling_admin.js'),
//        'css' => array(drupal_get_path('module', 'smartling') . '/css/smartling_admin.css'),
//      ),
    );

    $settings_forms = [
      'Drupal\smartling\Form\AdminAccountInfoSettingsForm' => 'Account info',
    ];
    //module_invoke_all('smartling_settings_form_info');

    foreach ($settings_forms as $machine_name => $title) {
      $form = \Drupal::formBuilder()->getForm($machine_name);//drupal_get_form($machine_name);
      $output['smartling'][] = $this->wrapInFieldset($form, $title);
    }

    return $output;
  }
}