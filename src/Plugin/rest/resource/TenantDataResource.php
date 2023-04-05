<?php

namespace Drupal\tenant\Plugin\rest\resource;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\GroupAccessResult;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\tenant\TenantDataInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Manage tenant properties.
 *
 * @RestResource(
 *   id = "tenant_data_resource",
 *   label = @Translation("Manage tenant data properties"),
 *   uri_paths = {
 *     "canonical" = "/api/tenants/{uuid}/properties/{module}"
 *   }
 * )
 */
class TenantDataResource extends ResourceBase {

  /**
   * The tenant.data storage.
   *
   * @var \Drupal\tenant\TenantDataInterface
   */
  private $tenantData;

  /**
   * The entity repository.
   *
   * @var EntityRepositoryInterface
   */
  private $entityRepository;

  /**
   * Group membership loader
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface
   */
  private $groupMembershipLoader;

  /**
   * Account of current user.
   *
   * @var Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array                     $configuration,
                              $plugin_id,
                              $plugin_definition,
    array                     $serializer_formats,
    LoggerInterface           $logger,
    TenantDataInterface       $tenant_data,
    EntityRepositoryInterface $entity_repository,
    GroupMembershipLoaderInterface $group_membership_loader,
    AccountInterface          $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->tenantData = $tenant_data;
    $this->entityRepository = $entity_repository;
    $this->groupMembershipLoader = $group_membership_loader;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('tenant.data'),
      $container->get('entity.repository'),
      $container->get('group.membership_loader'),
      $container->get('current_user')
    );
  }

  /**
   * Get tenant properties.
   */
  public function get($uuid, $module) {
    $tenant = $this->checkAccess($uuid, $module);
    $data = $this->tenantData->get($module, $tenant->id());
    return new ModifiedResourceResponse($data);
  }

  /**
   * Update tenant properties.
   */
  public function patch($uuid, $module, array $data) {
    $tenant = $this->checkAccess($uuid, $module, TRUE);

    foreach ($data as $name => $value) {
      $this->tenantData->set($module, $tenant->id(), $name, $value);
    }

    return new ModifiedResourceResponse(null, 201);
  }

  /**
   * Validate request parameters and load the tenant entity by the given tenant uuid.
   */
  private function checkAccess(string $uuid, string $module, bool $edit = FALSE): EntityInterface {
    if (empty($uuid) || empty($module)) {
      throw new BadRequestHttpException();
    }

    /* @var $tenant GroupInterface */
    if (!($tenant = $this->entityRepository->loadEntityByUuid('group', $uuid))) {
      throw new ResourceNotFoundException();
    }

    if (!$this->groupMembershipLoader->load($tenant, $this->currentUser)) {
      throw new AccessDeniedHttpException();
    }

    if ($edit && !GroupAccessResult::allowedIfHasGroupPermission($tenant, $this->currentUser, 'edit group')->isAllowed()) {
      throw new AccessDeniedHttpException();
    }

    return $tenant;
  }

}
