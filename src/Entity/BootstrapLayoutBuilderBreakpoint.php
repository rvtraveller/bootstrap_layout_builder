<?php

namespace Drupal\bootstrap_layout_builder\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\bootstrap_layout_builder\BootstrapLayoutBuilderBreakpointInterface;

/**
 * Defines the BootstrapLayoutBuilderBreakpoint config entity.
 *
 * @ConfigEntityType(
 *   id = "blb_breakpoint",
 *   label = @Translation("Bootstrap layout builder breakpoint"),
 *   label_collection = @Translation("Bootstrap layout builder breakpoints"),
 *   label_plural = @Translation("Bootstrap layout builder breakpoint"),
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *     "list_builder" = "Drupal\bootstrap_layout_builder\BootstrapLayoutBuilderBreakpointListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bootstrap_layout_builder\Form\BootstrapLayoutBuilderBreakpointForm",
 *       "edit" = "Drupal\bootstrap_layout_builder\Form\BootstrapLayoutBuilderBreakpointForm",
 *       "delete" = "Drupal\bootstrap_layout_builder\Form\BootstrapLayoutBuilderBreakpointDeleteForm"
 *     }
 *   },
 *   config_prefix = "breakpoint",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id" = "id",
 *     "label" = "label",
 *     "base_class" = "base_class",
 *     "status" = "status",
 *     "weight" = "weight",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/bootstrap-layout-builder/breakpoints/{blb_breakpoint}/edit",
 *     "delete-form" = "/admin/config/bootstrap-layout-builder/breakpoints/{blb_breakpoint}/delete",
 *     "collection" = "/admin/config/bootstrap-layout-builder/breakpoints",
 *     "add-form" = "/admin/config/bootstrap-layout-builder/breakpoints/add"
 *   }
 * )
 */
class BootstrapLayoutBuilderBreakpoint extends ConfigEntityBase implements BootstrapLayoutBuilderBreakpointInterface {

  /**
   * The Bootstrap layout Builder breakpoint ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Bootstrap layout Builder breakpoint label.
   *
   * @var string
   */
  protected $label;

  /**
   * The breakpoint base class.
   *
   * @var string
   */
  protected $base_class;

  /**
   * The breakpoint status.
   *
   * @var bool
   */
  protected $status;

  /**
   * Order of breakpoints on the config page & Layout Builder add/update forms.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * {@inheritdoc}
   */
  public function getBaseClass() {
    return $this->base_class;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->status;
  }

}
