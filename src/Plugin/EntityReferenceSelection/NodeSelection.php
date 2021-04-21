<?php

namespace Drupal\tenant\Plugin\EntityReferenceSelection;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\Plugin\EntityReferenceSelection\NodeSelection as DefaultNodeSelection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends default:node plugin with additional condition to output only nodes,
 * that are referenced to group over group_content
 *
 * @EntityReferenceSelection(
 *   id = "tenant:node",
 *   label = @Translation("Node selection"),
 *   base_plugin_label = @Translation("Tenant node: Node selection"),
 *   entity_types = {"node"},
 *   group = "tenant",
 *   weight = 1
 * )
 */
class NodeSelection extends DefaultNodeSelection {

  /**
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * Current group, if we are in a group context
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  private $group;

  /**
   * {@inheritdoc}
   */
  public function entityQueryAlter(SelectInterface $query) {
    parent::entityQueryAlter($query);

    // we take node IDs of all available node types. Restriction to specified
    // node type(s), if configured, already took place in parent plugin
    if ($this->group) {
      $group_content_enabled_ids = [];

      $plugins = $this->group->getGroupType()->getInstalledContentPlugins()->getIterator();
      foreach ($plugins as $id => $plugin) {
        /** @var \Drupal\group\Plugin\GroupContentEnablerInterface $plugin */
        if ($plugin->getEntityTypeId() == 'node') {
          $group_content_enabled_ids[] = $plugin->getContentTypeConfigId();
        }
      }

      // restrict to nids from group content, if at least one node content plugin found
      if ($group_content_enabled_ids) {
        $nids = $this->database
          ->select('group_content_field_data', 'gc')
          ->fields('gc', ['entity_id'])
          ->condition('gc.type', $group_content_enabled_ids, 'IN')
          ->condition('gc.gid', $this->group->id(), '=')
          ->execute()
          ->fetchCol(0);

        if ($nids) {
          $query->condition('base_table.nid', $nids, 'IN');
        }
      }
    }
  }

  /**
   * Return the current active database's master connection.
   *
   * @return \Drupal\Core\Database\Connection
   */
  protected function getDatabase() {
    return $this->database;
  }

  /**
   * Set database connection
   *
   * @param \Drupal\Core\Database\Connection
   */
  public function setDatabase(Connection $database) {
    $this->database = $database;
  }

  /**
   * Return current group, if available
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   */
  protected function getGroup() {
    return $this->group;
  }

  /**
   * Set current group
   *
   * @param \Drupal\group\Entity\GroupInterface|NULL $group
   */
  public function setGroup(GroupInterface $group = NULL) {
    $this->group = $group;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var self $plugin */
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    // set dependencies without overriding heavy parent constructor
    $plugin->setDatabase($container->get('database'));
    $plugin->setGroup($container->get('tenant.current_group_manager')->getCurrentGroup());

    return $plugin;
  }
}
