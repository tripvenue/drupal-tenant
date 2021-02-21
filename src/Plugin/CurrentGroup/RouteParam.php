<?php

namespace Drupal\tenant\Plugin\CurrentGroup;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\tenant\CurrentGroupPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the current_group.
 *
 * @CurrentGroup(
 *   id = "route_param",
 *   label = @Translation("Route param"),
 *   description = @Translation("Try to pluck current group from route match.")
 * )
 */
class RouteParam extends CurrentGroupPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Current route
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $route_match;

  /**
   * RouteParam constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param RouteMatchInterface $route_match
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->route_match = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function hasEntity() {
    return (bool) $this->route_match->getParameter('group');
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->route_match->getParameter('group');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

}
