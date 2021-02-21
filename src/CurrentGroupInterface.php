<?php

namespace Drupal\tenant;

/**
 * Interface for current_group plugins.
 */
interface CurrentGroupInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /**
   * Checks, whether the plugin provides group entity
   *
   * @return bool
   */
  public function hasEntity();

  /**
   * Returns group entity, provided by the plugin
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The group entity
   */
  public function getEntity();

}
