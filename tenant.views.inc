<?php

/**
 * Implements hook_views_data()
 */
function tenant_views_data() {
  $data = [];

  $data['node_field_data']['tenant_uid_autocomplete'] = [
    'real field' => 'uid',
    'filter' => [
      'id' => 'tenant_uid_autocomplete',
      'title' => t('Authored by (autocomplete)'),
      'help' => t('The user authoring the content of given group. Filtered by value(s) from text input')
    ],
  ];

  return $data;
}
