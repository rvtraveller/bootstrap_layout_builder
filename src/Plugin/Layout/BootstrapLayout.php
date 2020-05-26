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
    // Container.
    if ($this->configuration['container']) {
      if ($this->configuration['container_wrapper_classes']) {
        $build['container_wrapper']['#attributes']['class'] = $this->configuration['container_wrapper_classes'];
      }
      $build['container']['#attributes']['class'] = $this->configuration['container'];
    }

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
      // Container wrapper commonly used on container background and minor styling.
      'container_wrapper_classes' => '',
      // Container is the section wrapper.
      // Empty means no container else it reflect container type.
      // In bootstrap it will be 'container' or 'container-fluid'.
      'container' => '',
      // Section refer to the div that contains row in bootstrap.
      'section_classes' => '',
      // Region refer to the div that contains Col in bootstrap.
      'regions_classes' => $regions_classes,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['has_container'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Container'),
      '#default_value' => (int) !empty($this->configuration['container']) ? TRUE : FALSE,
    ];

    $container_types = [
      'container' => $this->t('Container'),
      'container-fluid' => $this->t('Container fluid'),
    ];

    $form['container_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Container type'),
      '#options' => $container_types,
      '#default_value' => !empty($this->configuration['container']) ? $this->configuration['container'] : 'container',
      '#states' => [
        'visible' => [
          ':input[name="layout_settings[has_container]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['has_container_wrapper'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Container Wrapper'),
      '#default_value' => (int) !empty($this->configuration['container_wrapper_classes']) ? TRUE : FALSE,
      '#states' => [
        'visible' => [
          ':input[name="layout_settings[has_container]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['container_wrapper_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Container wrapper classes'),
      '#description' => $this->t('Add classes separated by space. Ex: bg-warning py-5.'),
      '#default_value' => $this->configuration['container_wrapper_classes'],
      '#states' => [
        'visible' => [
          ':input[name="layout_settings[has_container_wrapper]"]' => ['checked' => TRUE],
        ],
      ],
    ];

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
    // Container type.
    $this->configuration['container'] = '';
    if ($form_state->getValue('has_container')) {
      $this->configuration['container'] = $form_state->getValue('container_type');
      // Container wrapper.
      if ($form_state->getValue('has_container_wrapper')) {
        $this->configuration['container_wrapper_classes'] = $form_state->getValue('container_wrapper_classes');
      }
    }

    $this->configuration['section_classes'] = $form_state->getValue('section_classes');
    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      $this->configuration['regions_classes'][$region_name] = $form_state->getValue('regions')[$region_name . '_classes'];
    }
  }

}
