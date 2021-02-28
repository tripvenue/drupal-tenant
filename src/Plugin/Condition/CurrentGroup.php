<?php

namespace Drupal\tenant\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\tenant\CurrentGroupPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Current group' condition
 *
 * @Condition(
 *   id = "current_group",
 *   label = @Translation("Current group"),
 * )
 *
 * @DCG prior to Drupal 8.7 the 'context_definitions' key was called 'context'.
 */
class CurrentGroup extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Current group plugin manager instance
   *
   * @var \Drupal\tenant\CurrentGroupPluginManager
   */
  private $currentGroupPluginManager;

  /**
   * Creates a new CurrentGroup instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\tenant\CurrentGroupPluginManager $current_group_plugin_manager
   *   The current group plugin manager for configuration of identifying
   *   current group strategies
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentGroupPluginManager $current_group_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentGroupPluginManager = $current_group_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.current_group')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['plugins' => []] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['plugins'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Sources'),
      '#default_value' => $this->configuration['plugins'],
      '#options' => [],
      '#description' => $this->t('If you select no source, the condition will always evaluate to FALSE'),
    ];

    $definitions = $this->currentGroupPluginManager->getDefinitions();
    foreach ($definitions as $plugin_id => $definition) {
      /** @var \Drupal\tenant\CurrentGroupPluginBase $plugin */
      $plugin = $this->currentGroupPluginManager->createInstance($plugin_id, $definition);
      $form['plugins']['#options'][$plugin->getPluginId()] = $plugin->label();
    }

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['plugins'] = array_filter($form_state->getValue('plugins'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Returns TRUE if current group is provided by current group manager');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $definitions = [];
    $current_group = NULL;
    // build definitions for relevant sources
    if ($this->configuration['plugins']) {
      foreach ($this->configuration['plugins'] as $plugin_id) {
        $definitions[$plugin_id] = $this->currentGroupPluginManager->getDefinition($plugin_id);
      }
    }
    // try to get current group from relevant sources
    foreach ($definitions as $plugin_id => $definition) {
      /** @var \Drupal\tenant\CurrentGroupPluginBase $plugin */
      $plugin = $this->currentGroupPluginManager->createInstance($plugin_id, $definition);
      if ($plugin->hasEntity()) {
        $current_group = $plugin->getEntity();
        break;
      }
    }

    return $current_group && !$this->isNegated();
  }

}
