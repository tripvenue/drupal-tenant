<?php

namespace Drupal\tenant\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\tenant\CurrentGroupPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Tenant routes.
 */
class TenantController extends ControllerBase {

  /**
   * Account for checking its permissions and group enrollment status
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $account;

  /**
   * @var \Drupal\tenant\CurrentGroupPluginManager
   */
  private $current_group_plugin_manager;

  /**
   * TenantController constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   * @param \Drupal\tenant\CurrentGroupPluginManager $current_group_plugin_manager
   */
  public function __construct(AccountProxyInterface $account, CurrentGroupPluginManager $current_group_plugin_manager) {
    $this->account = $account;
    $this->current_group_plugin_manager = $current_group_plugin_manager;
  }

  /**
   * Dispatches the front page request considering user permissions and
   * group membership status
   *
   * @return array
   */
  public function front() {
    // check, whether user has a relation to at least one group
    /** @var \Drupal\tenant\Plugin\CurrentGroup\CurrentUser $current_group_plugin */
    $definition = $this->current_group_plugin_manager->getDefinition('current_user');
    $current_group_plugin = $this->current_group_plugin_manager->createInstance('current_user', $definition);

    // user has group overview permission: ignore group specific checkings and
    // redirect to tenant group overview
    if ($this->account->hasPermission('access group overview')) {
      return $this->redirect('entity.group.collection');
    }
    // user is member of a group:
    // redirect to canonical group URL
    else if ($group = $current_group_plugin->getEntity()) {
      return $this->redirect('entity.group.canonical', ['group' => $group->id()]);
    }
    // user is groupless and without overview permission:
    // redirect to invitations view
    else {
      return $this->redirect('view.my_invitations.page_1', ['user' => $this->account->id()]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('plugin.manager.current_group')
    );
  }
}
