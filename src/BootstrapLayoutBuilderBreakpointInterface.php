<?php

namespace Drupal\bootstrap_layout_builder;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for Bootstrap Layout Builder breakpoints entities.
 */
interface BootstrapLayoutBuilderBreakpointInterface extends ConfigEntityInterface {

  /**
   * Returns the base class fo the breakpoint.
   *
   * @return string
   *   The base class of the breakpoint.
   */
  public function getBaseClass();

  /**
   * Returns the status of the breakpoint.
   *
   * @return bool
   *   Either "enabled" or "disabled".
   */
  public function getStatus();

}
