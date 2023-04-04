<?php

namespace Drupal\tenant;

use Drupal\Core\Database\Connection;

/**
 * Defines the tenant data service.
 */
class TenantData implements TenantDataInterface {

  /**
   * The database connection to use.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new tenant data service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection to use.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function get($module, $tenant_id = NULL, $name = NULL) {
    $query = $this->connection->select('tenant_data', 'td')
      ->fields('td')
      ->condition('module', $module);
    if (isset($tenant_id)) {
      $query->condition('tenant_id', $tenant_id);
    }
    if (isset($name)) {
      $query->condition('name', $name);
    }
    $result = $query->execute();
    // If $module, $tenant_id, and $name were passed, return the value.
    if (isset($name) && isset($tenant_id)) {
      $result = $result->fetchAllAssoc('tenant_id');
      if (isset($result[$tenant_id])) {
        return $result[$tenant_id]->serialized ? unserialize($result[$tenant_id]->value) : $result[$tenant_id]->value;
      }
      return NULL;
    }
    $return = [];
    // If $module and $tenant_id were passed, return data keyed by name.
    if (isset($tenant_id)) {
      foreach ($result as $record) {
        $return[$record->name] = ($record->serialized ? unserialize($record->value) : $record->value);
      }
      return $return;
    }
    // If $module and $name were passed, return data keyed by tenant_id.
    if (isset($name)) {
      foreach ($result as $record) {
        $return[$record->tenant_id] = ($record->serialized ? unserialize($record->value) : $record->value);
      }
      return $return;
    }
    // If only $module was passed, return data keyed by tenant_id and name.
    foreach ($result as $record) {
      $return[$record->tenant_id][$record->name] = ($record->serialized ? unserialize($record->value) : $record->value);
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function set($module, $tenant_id, $name, $value) {
    $serialized = (int) !is_scalar($value);
    if ($serialized) {
      $value = serialize($value);
    }
    $this->connection->merge('tenant_data')
      ->keys([
        'tenant_id' => $tenant_id,
        'module' => $module,
        'name' => $name,
      ])
      ->fields([
        'value' => $value,
        'serialized' => $serialized,
      ])
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function delete($module = NULL, $tenant_id = NULL, $name = NULL) {
    $query = $this->connection->delete('tenant_data');
    // Cast scalars to array so we can consistently use an IN condition.
    if (isset($module)) {
      $query->condition('module', (array) $module, 'IN');
    }
    if (isset($tenant_id)) {
      $query->condition('tenant_id', (array) $tenant_id, 'IN');
    }
    if (isset($name)) {
      $query->condition('name', (array) $name, 'IN');
    }
    $query->execute();
  }

}
