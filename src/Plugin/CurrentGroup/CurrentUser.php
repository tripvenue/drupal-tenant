<?php

namespace Drupal\tenant\Plugin\CurrentGroup;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\tenant\CurrentGroupPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the current_group.
 *
 * @CurrentGroup(
 *   id = "current_user",
 *   label = @Translation("Current user"),
 *   description = @Translation("Get current group from current user over first group membership relation, if it exist.")
 * )
 */
class CurrentUser extends CurrentGroupPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Current user
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $account;

  /**
   * Group membership loader
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface
   */
  private $groupMembershipLoader;

  /**
   * Lazy loaded group
   *
   * @var \Drupal\group\Entity\Group
   */
  private $group;

  /**
   * CurrentUser constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param AccountProxyInterface $account
   * @param GroupMembershipLoaderInterface $group_membership_loader
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $account, GroupMembershipLoaderInterface $group_membership_loader) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->account = $account;
    $this->groupMembershipLoader = $group_membership_loader;
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
    if (!$this->group) {
      if ($group_memberships = $this->groupMembershipLoader->loadByUser($this->account)) {
        /** @var \Drupal\group\GroupMembership $group_membership */
        $group_membership = reset($group_memberships);
        $this->group = $group_membership->getGroup();
      }
    }
    return $this->group;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('group.membership_loader')
    );
  }

}
