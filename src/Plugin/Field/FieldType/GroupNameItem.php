<?php

namespace Drupal\tenant\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Plugin implementation for displaying group memberships of users in their
 * profile display views
 *
 * @FieldType(
 *   id = "tenant_group_name",
 *   label = @Translation("Tenant user group"),
 *   default_widget = "basic_string",
 *   default_formatter = "basic_string",
 * )
 */
class GroupNameItem extends FieldItemList implements FieldItemListInterface {
  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  public function computeValue() {
    /** @var \Drupal\user\Entity\User $user */
    $user = $this->getEntity();

    // ignore users without memberships
    if ($user->isNew()) {
      return;
    }

    /** @var \Drupal\group\GroupMembershipLoader $group_membership_loader */
    $group_membership_loader = \Drupal::service('group.membership_loader');
    $group_memberships = $group_membership_loader->loadByUser($user);
    foreach ($group_memberships as $index => $group_membership) {
      $this->list[$index] = $this->createItem($index, $group_membership->getGroup()->label());
    }
  }
}
