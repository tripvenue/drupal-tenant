<?php

namespace Drupal\tenant\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Disables access to "Add member" form to avoid exposure of global user pool
 */
class GroupContentAddFormAccessChecker implements AccessInterface {

  /**
   * Access callback
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   */
  public function access(AccountInterface $account, RouteMatchInterface $routeMatch) {
    if ($routeMatch->getParameter('plugin_id') == 'group_membership') {
      if ($account->hasPermission('administer users')) {
        return AccessResult::allowed();
      }
      return AccessResult::forbidden();
    }
    return AccessResult::allowed();
  }
}
