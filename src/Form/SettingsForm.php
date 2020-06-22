<?php

namespace Drupal\bootstrap_layout_builder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Bootstrap Layout Builder settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'bootstrap_layout_builder.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bootstrap_layout_builder_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['hide_section_settings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide "Advanced Settings" Tab'),
      '#description' => $this->t('<img src="/' . drupal_get_path('module', 'bootstrap_layout_builder') . '/images/drupal-ui/toggle-advanced-settings.png" alt="Toggle Advanced Settings Tab Visibility" title="Toggle Advanced Settings Tab Visibility">'),
      '#default_value' => $config->get('hide_section_settings'),
    ];

    $form['style'] = [
      '#type' => 'details',
      '#title' => $this->t('Style'),
      '#open' => TRUE,
    ];

    $form['style']['background_colors'] = [
      '#type' => 'textarea',
      '#default_value' => $config->get('background_colors'),
      '#title' => $this->t('Background colors (classes)'),
      '#description' => $this->t('<p>Enter one value per line, in the format <b>key|label</b> where <em>key</em> is the CSS class name (without the .), and <em>label</em> is the human readable name of the background.</p>'),
      '#cols' => 60,
      '#rows' => 5,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('hide_section_settings', $form_state->getValue('hide_section_settings'))
      ->set('background_colors', $form_state->getValue('background_colors'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
