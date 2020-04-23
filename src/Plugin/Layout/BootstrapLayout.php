<?php

namespace Drupal\bootstrap_layout_builder\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Form\FormStateInterface;

/**
 * A layout from our bootstrap layout builder.
 *
 * @Layout(
 *   id = "bootstrap_layout_builder",
 *   deriver = "Drupal\bootstrap_layout_builder\Plugin\Deriver\BootstrapLayoutDeriver"
 * )
 */
class BootstrapLayout extends LayoutDefault {

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);

    // Section Classes.
    $section_classes = [];
    if ($this->configuration['section_classes']) {
      $section_classes = explode(' ', $this->configuration['section_classes']);
      $build['#attributes']['class'] = $section_classes;
    }

    // Regions classes.
    if ($this->configuration['regions_classes']) {
      foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
        $region_classes = explode(' ', $this->configuration['regions_classes'][$region_name]);
        $build[$region_name]['#attributes']['class'] = $region_classes;
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_configuration = parent::defaultConfiguration();

    $regions_classes = [];
    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      $regions_classes[$region_name] = '';
    }

    return $default_configuration + [
      'section_classes' => '',
      'regions_classes' => $regions_classes,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['section_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Row classes'),
      '#description' => $this->t('Row has "row" class, you can add more classes separated by space. Ex: no-gutters py-3.'),
      '#default_value' => $this->configuration['section_classes'],
    ];

    $form['regions'] = [
      '#type' => 'details',
      '#title' => $this->t('Columns Settings'),
      '#description' => $this->t('Add classes separated by space. Ex: col mb-5 py-3.'),
      '#open' => TRUE,
    ];

    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      $form['regions'][$region_name . '_classes'] = [
        '#type' => 'textfield',
        '#title' => $this->getPluginDefinition()->getRegionLabels()[$region_name] . ' ' . $this->t('classes'),
        '#default_value' => $this->configuration['regions_classes'][$region_name],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['section_classes'] = $form_state->getValue('section_classes');
    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      $this->configuration['regions_classes'][$region_name] = $form_state->getValue('regions')[$region_name . '_classes'];
    }
  }

}
