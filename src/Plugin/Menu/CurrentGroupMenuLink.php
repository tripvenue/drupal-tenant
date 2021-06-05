<?php

namespace Drupal\tenant\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\tenant\CurrentGroupManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allows to define menu links with group in route params
 */
class CurrentGroupMenuLink extends MenuLinkDefault {

  /**
   * Current group or NULL, if current group was
   *
   * @var \Drupal\group\Entity\GroupInterface|null
   */
  protected $group;

  /**
   * Constructs the plugin object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $static_override
   *   The static override storage.
   * @param \Drupal\tenant\CurrentGroupManager $current_group_manager
   *   The manager for returning current group instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StaticMenuLinkOverridesInterface $static_override, CurrentGroupManager $current_group_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $static_override);
    $this->group = $current_group_manager->getCurrentGroup();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu_link.static.overrides'),
      $container->get('tenant.current_group_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters() {
    $route_parameters = parent::getRouteParameters();
    if ($this->group) {
      // set current group in route parameters, if group could be identified
      $route_parameters['group'] = $this->group->id();
    }
    return $route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = ['url'];
    if ($this->group) {
      $cache_tags[] = "group:{$this->group->id()}";
    }
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [
      'route.group',
      'user.group_permissions'
    ];
  }
}
