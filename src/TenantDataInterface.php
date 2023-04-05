<?php

namespace Drupal\tenant;

/**
 * Defines the tenant data service interface.
 */
interface TenantDataInterface {

  /**
   * Returns data stored for a tenant.
   *
   * @param string $module
   *   The name of the module the data is associated with.
   * @param int $tenant_id
   *   (optional) The tenant ID the data is associated with.
   * @param string $name
   *   (optional) The name of the data key.
   *
   * @return mixed|array
   *   The requested tenant data, depending on the arguments passed:
   *   - For $module, $name, and $tenant_id, the stored value is returned, or NULL if
   *     no value was found.
   *   - For $module and $tenant_id, an associative array is returned that contains
   *     the stored data name/value pairs.
   *   - For $module and $name, an associative array is returned whose keys are
   *     tenant IDs and whose values contain the stored values.
   *   - For $module only, an associative array is returned that contains all
   *     existing data for $module in all tenants, keyed first by tenant ID
   *     and $name second.
   */
  public function get($module, $tenant_id = NULL, $name = NULL);

  /**
   * Stores data for a tenant.
   *
   * @param string $module
   *   The name of the module the data is associated with.
   * @param int $tenant_id
   *   The tenant ID the data is associated with.
   * @param string $name
   *   The name of the data key.
   * @param mixed $value
   *   The value to store. Non-scalar values are serialized automatically.
   */
  public function set($module, $tenant_id, $name, $value);

  /**
   * Deletes data stored for a tenant.
   *
   * @param string|array $module
   *   (optional) The name of the module the data is associated with. Can also
   *   be an array to delete the data of multiple modules.
   * @param int|array $tenant_id
   *   (optional) The tenant ID the data is associated with. If omitted,
   *   all data for $module is deleted. Can also be an array of IDs to delete
   *   the data of multiple tenants.
   * @param string $name
   *   (optional) The name of the data key. If omitted, all data associated with
   *   $module and $tenant_id is deleted.
   */
  public function delete($module = NULL, $tenant_id = NULL, $name = NULL);

}
