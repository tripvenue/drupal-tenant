<?php

namespace Drupal\tenant\Form\Helper;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the tenant.form_helper.user_login service
 */
class UserLogin {

  /**
   * Alters user login form
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    // register custom form submit
    $form['#submit'][] = [$this, 'formSubmit'];
  }

  /**
   * Executed on user login form submit
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function formSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('tenant.page.front');
  }
}
