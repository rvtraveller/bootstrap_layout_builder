<?php

namespace Drupal\bootstrap_layout_builder\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\bootstrap_styles\StylesGroup\StylesGroupManager;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The styles group plugin manager.
   *
   * @var \Drupal\bootstrap_styles\StylesGroup\StylesGroupManager
   */
  protected $stylesGroupManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\bootstrap_styles\StylesGroup\StylesGroupManager $styles_group_manager
   *   The styles group plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entity_type_manager, StylesGroupManager $styles_group_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entity_type_manager;
    $this->stylesGroupManager = $styles_group_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.bootstrap_styles_group')
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

      $build['container_wrapper'] = $this->stylesGroupManager->buildStyles([], $this->configuration['container_wrapper']['bootstrap_styles']);

      if ($this->configuration['container_wrapper_classes']) {
        $build['container_wrapper']['#attributes']['class'][] = $this->configuration['container_wrapper_classes'];
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
        $region_classes = $this->configuration['regions_classes'][$region_name];
        if ($this->configuration['layout_regions_classes'] && isset($this->configuration['layout_regions_classes'][$region_name])) {
          $build[$region_name]['#attributes']['class'] = $this->configuration['layout_regions_classes'][$region_name];
        }
        $build[$region_name]['#attributes']['class'][] = $region_classes;
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
      // Container wrapper.
      'container_wrapper' => [
        // The dynamic bootstrap styles storage.
        'bootstrap_styles' => [],
      ],
      // Add background color to container wrapper.
      'container_wrapper_bg_color_class' => '',
      // Add background media to container wrapper.
      'container_wrapper_bg_media' => NULL,
      // Container is the section wrapper.
      // Empty means no container else it reflect container type.
      // In bootstrap it will be 'container' or 'container-fluid'.
      'container' => '',
      // Section refer to the div that contains row in bootstrap.
      'section_classes' => '',
      // Region refer to the div that contains Col in bootstrap "Advanced mode".
      'regions_classes' => $regions_classes,
      // Array of breakpoints and the value of its option.
      'breakpoints' => [],
      // The region refer to the div that contains Col in bootstrap.
      'layout_regions_classes' => [],
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
      if ($line && isset($line[0]) && isset($line[1])) {
        $options[$line[0]] = $line[1];
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Our main set of tabs.
    $form['ui'] = [
      '#type' => 'container',
      '#weight' => -100,
      '#attributes' => [
        'id' => 'blb_ui',
      ],
    ];

    $tabs = [
      [
        'machine_name' => 'layout',
        'icon' => 'layout.svg',
        'title' => $this->t('Layout'),
        'active' => TRUE,
      ],
      [
        'machine_name' => 'appearance',
        'icon' => 'appearance.svg',
        'title' => $this->t('Style'),
      ],
      [
        'machine_name' => 'settings',
        'icon' => 'settings.svg',
        'title' => $this->t('Settings'),
      ],
    ];

    // Create our tabs from above.
    $form['ui']['nav_tabs'] = [
      '#type' => 'html_tag',
      '#tag' => 'ul',
      '#attributes' => [
        'class' => 'blb_nav-tabs',
        'id' => 'blb_nav-tabs',
        'role' => 'tablist',
      ],
    ];

    $form['ui']['tab_content'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'blb_tab-content',
        'id' => 'blb_tabContent',
      ],
    ];

    // Create our tab & tab panes.
    foreach ($tabs as $tab) {
      $form['ui']['nav_tabs'][$tab['machine_name']] = [
        '#type' => 'inline_template',
        '#template' => '<li><a data-target="{{ target|clean_class }}" class="{{active}}">{{ icon }}<div class="blb_tooltip" data-placement="bottom" role="tooltip">{{ title }}</div></a></li>',
        '#context' => [
          'title' => $tab['title'],
          'target' => $tab['machine_name'],
          'active' => isset($tab['active']) && $tab['active'] == TRUE ? 'active' : '',
          'icon' => t('<img class="blb_icon" src="/' . drupal_get_path('module', 'bootstrap_layout_builder') . '/images/ui/' . ($tab['icon'] ? $tab['icon'] : 'default.svg') . '" />'),
        ],
      ];

      $form['ui']['tab_content'][$tab['machine_name']] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'blb_tab-pane',
            'blb_tab-pane--' . $tab['machine_name'],
            isset($tab['active']) && $tab['active'] == TRUE ? 'active' : '',
          ],
        ],
      ];
    }

    // Check if section settings visible.
    $form['ui']['tab_content']['layout']['has_container'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Container'),
      '#default_value' => (int) !empty($this->configuration['container']) ? TRUE : FALSE,
    ];

    $container_types = [
      'container' => $this->t('Boxed'),
      'container-fluid' => $this->t('Full'),
      'w-100' => $this->t('Flush'),
    ];

    $form['ui']['tab_content']['layout']['container_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Container type'),
      '#title_display' => 'invisible',
      '#options' => $container_types,
      '#default_value' => !empty($this->configuration['container']) ? $this->configuration['container'] : 'container',
      '#attributes' => [
        'class' => ['blb_container_type'],
      ],
      '#states' => [
        'visible' => [
          ':input[name="layout_settings[ui][tab_content][layout][has_container]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['ui']['tab_content']['layout']['remove_gutters'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('No Gutters'),
      '#default_value' => (int) !empty($this->configuration['remove_gutters']) ? TRUE : FALSE,
    ];

    $layout_id = $this->getPluginDefinition()->id();
    $breakpoints = $this->entityTypeManager->getStorage('blb_breakpoint')->getQuery()->sort('weight', 'ASC')->execute();
    foreach ($breakpoints as $breakpoint_id) {
      $breakpoint = $this->entityTypeManager->getStorage('blb_breakpoint')->load($breakpoint_id);
      $layout_options = $breakpoint->getLayoutOptions($layout_id);
      $default_value = '';
      if ($this->configuration['breakpoints'] && isset($this->configuration['breakpoints'][$breakpoint_id])) {
        $default_value = $this->configuration['breakpoints'][$breakpoint_id];
      }
      $form['ui']['tab_content']['layout']['breakpoints'][$breakpoint_id] = [
        '#type' => 'radios',
        '#title' => $breakpoint->label(),
        '#options' => $layout_options,
        '#default_value' => $default_value,
        '#validated' => TRUE,
        '#attributes' => [
          'class' => ['blb_breakpoint_cols'],
        ],
      ];
    }

    // Container wrapper styling.
    $form['ui']['tab_content']['appearance'] = $this->stylesGroupManager->buildStylesFormElements($form['ui']['tab_content']['appearance'], $form_state, $this->configuration['container_wrapper']['bootstrap_styles']);
    // Alter styling elements.
    $form['ui']['tab_content']['appearance']['background']['#states'] = [
      'visible' => [
        ':input[name="layout_settings[ui][tab_content][layout][has_container]"]' => ['checked' => TRUE],
      ],
    ];
    $form['ui']['tab_content']['appearance']['background']['background_color']['#attributes']['class'][] = 'bootstrap_layout_builder_bg_color';
    // @TODO Map old configs.

    // Move default admin label input to setting tab.
    $form['ui']['tab_content']['settings']['label'] = $form['label'];
    unset($form['label']);

    // Advanced Settings.
    if (!$this->sectionSettingsIsHidden()) {
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
    }

    // Attach the Bootstrap Layout Builder base library.
    $form['#attached']['library'][] = 'bootstrap_layout_builder/base';

    return $form;
  }

  /**
   * Returns region class of a breakpoint.
   *
   * @param int $key
   *   The position of region.
   * @param array $breakpoints
   *   The layout active breakpoints.
   *
   * @return array
   *   The region classes of all breakpoints.
   */
  public function getRegionClasses(int $key, array $breakpoints) {
    $classes = [];
    foreach ($breakpoints as $breakpoint_id => $strucutre_id) {
      $breakpoint = $this->entityTypeManager->getStorage('blb_breakpoint')->load($breakpoint_id);
      $classes[] = $breakpoint->getClassByPosition($key, $strucutre_id);
    }
    return $classes;
  }

  /**
   * Save breakpoints to the configuration.
   *
   * @param array $breakpoints
   *   The layout active breakpoints.
   */
  public function saveBreakpoints(array $breakpoints) {
    $this->configuration['breakpoints'] = $breakpoints;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    // The tabs structure.
    $layout_tab = ['ui', 'tab_content', 'layout'];
    $style_tab = ['ui', 'tab_content', 'appearance'];
    $settings_tab = ['ui', 'tab_content', 'settings'];

    // Save sction label.
    $this->configuration['label'] = $form_state->getValue(array_merge($settings_tab, ['label']));

    // Container type.
    $this->configuration['container'] = '';
    if ($form_state->getValue(array_merge($layout_tab, ['has_container']))) {
      $this->configuration['container'] = $form_state->getValue(array_merge($layout_tab, ['container_type']));

      // Styles tab.
      $this->configuration['container_wrapper']['bootstrap_styles'] = $this->stylesGroupManager->submitStylesFormElements($form['ui']['tab_content']['appearance'], $form_state, $style_tab, $this->configuration['container_wrapper']['bootstrap_styles']);

      // Container classes from advanced mode.
      if (!$this->sectionSettingsIsHidden()) {
        $this->configuration['container_wrapper_classes'] = $form_state->getValue(array_merge($settings_tab, ['container', 'container_wrapper_classes']));
      }
    }

    // Gutter Classes
    $this->configuration['remove_gutters'] = $form_state->getValue(array_merge($layout_tab, ['remove_gutters']));

    // Row classes from advanced mode.
    if (!$this->sectionSettingsIsHidden()) {
      $this->configuration['section_classes'] = $form_state->getValue(array_merge($settings_tab, ['row', 'section_classes']));
    }

    $breakpoints = $form_state->getValue(array_merge($layout_tab, ['breakpoints']));
    // Save breakpoints configuration.
    $this->saveBreakpoints($breakpoints);

    foreach ($this->getPluginDefinition()->getRegionNames() as $key => $region_name) {
      // Save layout region classes.
      $this->configuration['layout_regions_classes'][$region_name] = $this->getRegionClasses($key, $breakpoints);
      // Cols classes from advanced mode.
      if (!$this->sectionSettingsIsHidden()) {
        $this->configuration['regions_classes'][$region_name] = $form_state->getValue(array_merge($settings_tab, ['regions', $region_name . '_classes']));
      }
    }

  }

}
