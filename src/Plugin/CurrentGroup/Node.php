<?php

namespace Drupal\tenant\Plugin\CurrentGroup;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\group\Entity\Storage\GroupContentStorageInterface;
use Drupal\tenant\CurrentGroupPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the current_group.
 *
 * @CurrentGroup(
 *   id = "node",
 *   label = @Translation("Node"),
 *   description = @Translation("Get current group from node over first group_content relation, if it exist.")
 * )
 */
class Node extends CurrentGroupPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Group content storage to identify Group from Node via GroupContent reference
   *
   * @var \Drupal\group\Entity\Storage\GroupContentStorageInterface $group_content_storage
   */
  private $group_content_storage;

  /**
   * Current Node entity from route or NULL
   *
   * @var \Drupal\node\NodeInterface|NULL $node
   */
  private $node;

  /**
   * RouteParam constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param GroupContentStorageInterface $group_content_storage
   * @param RouteMatchInterface $route_match
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GroupContentStorageInterface $group_content_storage, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->group_content_storage = $group_content_storage;
    $this->node = $route_match->getParameter('node');
  }

  /**
   * {@inheritdoc}
   */
  public function hasEntity() {
    return (bool) $this->getEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    if ($this->node) {
      if ($group_contents = $this->group_content_storage->loadByEntity($this->node)) {
        /** @var \Drupal\group\Entity\GroupContentInterface $group_content */
        $group_content = current($group_contents);
        return $group_content->getGroup();
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('group_content'),
      $container->get('current_route_match')
    );
  }

}
