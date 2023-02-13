<?php

namespace Drupal\tenant\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Tenant route subscriber.
 */
class AccessRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // handle group content add form access
    if ($route = $collection->get('entity.group_relationship.add_form')) {
      $route->setRequirement('_custom_access', 'Drupal\tenant\Access\GroupContentAddFormAccessChecker::access');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();

    // Use a lower priority than \Drupal\views\EventSubscriber\RouteSubscriber
    // to ensure the requirement will be added to its routes.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -300];

    return $events;
  }

}
