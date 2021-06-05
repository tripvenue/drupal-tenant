<?php

namespace Drupal\tenant\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Plugin\views\filter\Name;

/**
 * Filter handler for group based usernames.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("tenant_uid_autocomplete")
 */
class UserAutocomplete extends Name {

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);
    $form['value']['#selection_handler'] = 'tenant:user';
  }
}
