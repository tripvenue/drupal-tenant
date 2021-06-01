<?php

namespace Drupal\tenant\Plugin\EntityReferenceSelection;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\Plugin\EntityReferenceSelection\UserSelection as DefaultUserSelection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends default:user plugin with additional conditions to output only active
 * group members without current user.
 *
 * @EntityReferenceSelection(
 *   id = "tenant:user",
 *   label = @Translation("User selection"),
 *   base_plugin_label = @Translation("Tenant user: User selection"),
 *   entity_types = {"user"},
 *   group = "tenant",
 *   weight = 1
 * )
 */
class UserSelection extends DefaultUserSelection {

  /**
   * Current group, if in a group context
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  private $group;

  /**
   * {@inheritdoc}
   */
  public function entityQueryAlter(SelectInterface $query) {
    parent::entityQueryAlter($query);

    // filter by group members, if we are in a group context
    if ($this->getGroup()) {
      $uids = [];

      $members = $this->group->getMembers();
      foreach ($members as $member) {
        $user = $member->getUser();
        $uids[$user->id()] = $user->id();
      }

      // remove current user from the list
      unset($uids[$this->currentUser->id()]);

      if ($uids) {
        $query->condition('base_table.uid', $uids, 'IN');
      }
    }

    // restrict to active users regardless previous considerations
    $query->condition('users_field_data.status', 1);
  }

  /**
   * Return current group, if available
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   */
  private function getGroup() {
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

    // set current group without overriding heavy parent constructor
    $plugin->setGroup($container->get('tenant.current_group_manager')->getCurrentGroup());

    return $plugin;
  }

}
