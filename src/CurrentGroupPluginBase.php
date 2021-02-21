<?php

namespace Drupal\tenant;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for current_group plugins.
 */
abstract class CurrentGroupPluginBase extends PluginBase implements CurrentGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
