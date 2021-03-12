<?php

/**
 * Implements hook_views_data()
 */
function tenant_views_data() {
  $data = [];

  $data['node_field_data']['uid_tenant'] = [
    'real field' => 'uid',
    'filter' => [
      'id' => 'group_user_name',
      'title' => t('Tenant: Authored by (autocomplete)'),
      'help' => t('Content author, restricted to current group')
    ],
  ];

  return $data;
}
