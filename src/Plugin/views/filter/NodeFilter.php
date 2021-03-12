<?php

namespace Drupal\tenant\Plugin\views\filter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\tenant\CurrentGroupManager;
use Drupal\views\Plugin\views\filter\NumericFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NodeFilter
 * @package Drupal\tenant\Plugin\views\filter
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("tenant_node_filter")
 */
class NodeFilter extends NumericFilter implements ContainerFactoryPluginInterface {

  /**
   * Current group or NULL, if no match
   *
   * @var \Drupal\group\Entity\GroupInterface|NULL
   */
  private $group;

  /**
   * NodeFilter constructor.
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\tenant\CurrentGroupManager $current_group_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentGroupManager $current_group_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->group = $current_group_manager->getCurrentGroup();
  }

  public function query() {
    parent::query();
  }

  protected function opSimple($field) {
    parent::opSimple($field);

    // TODO add additional condition to restrict by nids from current group
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tenant.current_group_manager')
    );
  }
}
