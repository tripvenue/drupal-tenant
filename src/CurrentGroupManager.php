<?php

namespace Drupal\tenant;

/**
 * Class responsible for providing current group instance
 */
class CurrentGroupManager {

  /**
   * The plugin.manager.current_group service.
   *
   * @var CurrentGroupPluginManager
   */
  protected $pluginManager;

  /**
   * The matched current group
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $currentGroup;

  /**
   * Constructs a CurrentGroupManager object.
   *
   * @param CurrentGroupPluginManager $plugin_manager
   *   The plugin.manager.current_group service.
   */
  public function __construct(CurrentGroupPluginManager $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * Returns first matched entity in a CurrentGroup plugin collection
   * Returns NULL, if no matches
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   */
  public function getCurrentGroup() {
    if ($this->currentGroup) {
      return $this->currentGroup;
    }

    $definitions = $this->pluginManager->getDefinitions();
    foreach ($definitions as $definition) {
      /** @var CurrentGroupInterface $plugin */
      $plugin = $this->pluginManager->createInstance($definition['id'], $definition);
      if ($plugin->hasEntity()) {
        $this->currentGroup = $plugin->getEntity();
        return $this->currentGroup;
      }
    }
  }

}
