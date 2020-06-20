<?php

namespace Drupal\bootstrap_layout_builder\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A layout from our bootstrap layout builder.
 *
 * @Layout(
 *   id = "bootstrap_layout_builder",
 *   deriver = "Drupal\bootstrap_layout_builder\Plugin\Deriver\BootstrapLayoutDeriver"
 * )
 */
class BootstrapLayout extends LayoutDefault implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    // Container.
    if ($this->configuration['container']) {
      $build['container']['#attributes']['class'] = $this->configuration['container'];

      if ($this->configuration['container_wrapper_bg_color_class'] || $this->configuration['container_wrapper_classes']) {
        $container_wrapper_classes = '';
        if ($this->configuration['container_wrapper_bg_color_class']) {
          $container_wrapper_classes .= $this->configuration['container_wrapper_bg_color_class'];
        }

        if ($this->configuration['container_wrapper_classes']) {
          // Add space after the last class.
          if ($container_wrapper_classes) {
            $container_wrapper_classes = $container_wrapper_classes . ' ';
          }
          $container_wrapper_classes .= $this->configuration['container_wrapper_classes'];
        }
        $build['container_wrapper']['#attributes']['class'] = $container_wrapper_classes;
      }
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
      // Add background color to container wrapper.
      'container_wrapper_bg_color_class' => '',
      // Add background media to container wrapper.
      'container_wrapper_bg_media' => 0,
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
   * Helper function to get section settings show/hide status.
   *
   * @return bool
   *   Section settings status.
   */
  public function sectionSettingsIsHidden() {
    $config = $this->configFactory->get('bootstrap_layout_builder.settings');
    $hide_section_settings = FALSE;
    if ($config->get('hide_section_settings')) {
      $hide_section_settings = (bool) $config->get('hide_section_settings');
    }
    return $hide_section_settings;
  }

  /**
   * Helper function to get the options of given style name.
   *
   * @param string $name
   *   A config style name like background_color.
   *
   * @return array
   *   Array of key => value of style name options.
   */
  public function getStyleOptions(string $name) {
    $config = $this->configFactory->get('bootstrap_layout_builder.settings');
    $options = [];
    $config_options = $config->get($name);

    $options = ['_none' => t('N/A')];
    $lines = explode(PHP_EOL, $config_options);
    foreach ($lines as $line) {
      $line = explode('|', $line);
      $options[$line[0]] = $line[1];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['#attributes']['classes'][] = 'bootstrap_layout_builder_ui';

    if (!$this->sectionSettingsIsHidden()) {

      // Our main set of tabs
      $form['ui'] = array(
        '#type' => 'container',
        '#weight' => -100,
        '#attributes' => [
          'class' => 'blb_ui',
        ],
        '#states' => [
          'visible' => [
            ':input[name="layout_settings[has_container]"]' => ['checked' => TRUE],
          ],
        ],
      );

      $tabs = array(
        array(
          'machine_name' => 'appearance',
          'icon' => 'appearance.svg',
          'title' => $this->t('Look & Feel'),
          'active' => true,
        ),
        array(
          'machine_name' => 'layout',
          'icon' => 'layout.svg',
          'title' => $this->t('Layout'),
        ),
        array(
          'machine_name' => 'effects',
          'icon' => 'effects.svg',
          'title' => $this->t('Effects'),
        ),
        array(
          'machine_name' => 'settings',
          'icon' => 'settings.svg',
          'title' => $this->t('Advanced Settings'),
        ),
      );

      // Create our tabs from above.
      $form['ui']['nav_tabs'] = array(
        '#type' => 'html_tag',
        '#tag' => 'ul',
        '#attributes' => [
          'class' => 'blb_nav-tabs',
          'id' => 'blb_nav-tabs',
          'role' => 'tablist'
        ],
      );

      $form['ui']['tab_content'] = array(
        '#type' => 'container',
        '#attributes' => [
          'class' => 'blb_tab-content',
          'id' => 'blb_tabContent'
        ],
      );

      // Create our tab & tab panes.
      foreach ($tabs as $tab) {
        $form['ui']['nav_tabs'][$tab['machine_name']] = array(
          '#type' => 'inline_template',
          '#template' => '<li><a data-target="{{ target|clean_class }}" class="{{active}}">{{ icon }}<div class="blb_tooltip" role="tooltip">{{ title }}</div></a></li>',
          '#context' => [
            'title' => $tab['title'],
            'target' => $tab['machine_name'],
            'active' => $tab['active'] == true ? 'active' : '',
            'icon' => t('<img class="blb_icon" src="/' . drupal_get_path('module', 'bootstrap_layout_builder') . '/images/ui/' . ($tab['icon'] ? $tab['icon'] : 'default.svg') . '" />'),
          ],
        );

        $form['ui']['tab_content'][$tab['machine_name']] = array(
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              'blb_tab-pane',
              'blb_tab-pane--' . $tab['machine_name'],
              $tab['active'] == true ? 'active' : '',
            ],
          ],
        );
      }

      // Check if section settings visible.
      $form['ui']['tab_content']['layout']['has_container'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Add Container'),
        '#default_value' => (int) !empty($this->configuration['container']) ? TRUE : FALSE,
      ];

      $container_types = [
        'container' => $this->t('Contained'),
        'container-fluid' => $this->t('Full Width'),
      ];

      $form['ui']['tab_content']['layout']['container_type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Container type'),
        '#options' => $container_types,
        '#default_value' => !empty($this->configuration['container']) ? $this->configuration['container'] : 'container',
        '#attributes' => [
          'class' => ['lbl_container_type'],
        ],
        '#states' => [
          'visible' => [
            ':input[name="layout_settings[ui][tab_content][layout][has_container]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      // Background Colors
      $form['ui']['tab_content']['appearance']['container_wrapper_bg_color_class'] = [
        '#type' => 'radios',
        '#options' => $this->getStyleOptions('background_colors'),
        '#title' => $this->t('Background color'),
        '#default_value' => $this->configuration['container_wrapper_bg_color_class'],
        '#attributes' => [
          'class' => ['bootstrap_layout_builder_bg_color'],
        ],
        '#states' => [
          'visible' => [
            ':input[name="layout_settings[has_container_wrapper]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['ui']['tab_content']['appearance']['container_wrapper_bg_media'] = [
        '#type' => 'media_library',
        '#title' => $this->t('Background media'),
        '#description' => $this->t('Background media'),
        '#allowed_bundles' => ['image', 'video'],
        '#default_value' => $this->configuration['container_wrapper_bg_media'] ?: 0,
        '#prefix' => '<hr />',
      ];

      // Advanced Settings > Classes
      $form['ui']['tab_content']['settings']['container'] = [
        '#type' => 'details',
        '#title' => $this->t('Container Settings'),
        '#open' => FALSE,
      ];

      $form['ui']['tab_content']['settings']['container']['container_wrapper_classes'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Container wrapper classes'),
        '#description' => $this->t('Add classes separated by space. Ex: bg-warning py-5.'),
        '#default_value' => $this->configuration['container_wrapper_classes'],
      ];

      $form['ui']['tab_content']['settings']['row'] = [
        '#type' => 'details',
        '#title' => $this->t('Row Settings'),
        '#description' => $this->t('Add classes separated by space. Ex: col mb-5 py-3.'),
        '#open' => FALSE,
      ];

      $form['ui']['tab_content']['settings']['row']['section_classes'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Row classes'),
        '#description' => $this->t('Row has "row" class, you can add more classes separated by space. Ex: no-gutters py-3.'),
        '#default_value' => $this->configuration['section_classes'],
      ];

      $form['ui']['tab_content']['settings']['regions'] = [
        '#type' => 'details',
        '#title' => $this->t('Columns Settings'),
        '#description' => $this->t('Add classes separated by space. Ex: col mb-5 py-3.'),
        '#open' => FALSE,
      ];

      foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
        $form['ui']['tab_content']['settings']['regions'][$region_name . '_classes'] = [
          '#type' => 'textfield',
          '#title' => $this->getPluginDefinition()->getRegionLabels()[$region_name] . ' ' . $this->t('classes'),
          '#default_value' => $this->configuration['regions_classes'][$region_name],
        ];
      }


      // Effects
      $form['ui']['tab_content']['effects']['message'] = [
        '#type' => 'inline_template',
        '#template' => '<small>Transition Effects Coming Soon...</small>',
      ];
    }

    // Attache the Bootstrap Layout Builder base libraray.
    $form['#attached']['library'][] = 'bootstrap_layout_builder/base';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    // Check if section settings visible.
    if (!$this->sectionSettingsIsHidden()) {
      // Container type.
      $this->configuration['container'] = '';
      if ($form_state->getValue('has_container')) {
        $this->configuration['container'] = $form_state->getValue('container_type');
        // Container wrapper.
        $this->configuration['container_wrapper_bg_color_class'] = $form_state->getValue('background')['container_wrapper_bg_color_class'];
        $this->configuration['container_wrapper_bg_media'] = $form_state->getValue('background')['container_wrapper_bg_media'];
        $this->configuration['container_wrapper_classes'] = $form_state->getValue('container_wrapper_classes');
      }

      $this->configuration['section_classes'] = $form_state->getValue('section_classes');
      foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
        $this->configuration['regions_classes'][$region_name] = $form_state->getValue('regions')[$region_name . '_classes'];
      }
    }
  }

}
