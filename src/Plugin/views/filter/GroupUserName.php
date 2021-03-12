<?php

namespace Drupal\tenant\Plugin\views\filter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\tenant\CurrentGroupManager;
use Drupal\user\Plugin\views\filter\Name;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter handler for group based usernames.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_user_name")
 */
class GroupUserName extends Name implements ContainerFactoryPluginInterface {

  /**
   * Current group or NULL, if no match
   *
   * @var \Drupal\group\Entity\GroupInterface|NULL
   */
  private $group;

  /**
   * GroupUserName constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\tenant\CurrentGroupManager $current_group_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentGroupManager $current_group_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->group = $current_group_manager->getCurrentGroup();
  }

  protected function opSimple() {
    parent::opeSimple();

    // TODO add additional condition to restrict by uids from current group
  }

  protected function opEmpty() {
    parent::opEmpty();

    // TODO add additional condition to restrict by uids from current group
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
