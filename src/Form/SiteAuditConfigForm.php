<?php

declare(strict_types = 1);

namespace Drupal\site_audit\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\site_audit\Plugin\SiteAuditReportManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class SiteAuditConfigForm extends ConfigFormBase {


  /**
   * @var \Drupal\site_audit\Plugin\SiteAuditReportManager
   */
  protected $report_plugin_manager;

  /**
   * SiteAuditConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\site_audit\Plugin\SiteAuditReportManager $site_audit_report_manager
   */
  public function __construct(ConfigFactoryInterface $config_factory, SiteAuditReportManager $site_audit_report_manager) {
    parent::__construct($config_factory);
    $this->report_plugin_manager = $site_audit_report_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.site_audit_report')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['site_audit.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'site_audit_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $saved_options = $this->config('site_audit.settings')->get('reports');
    $reports = $this->report_plugin_manager->getDefinitions();
    foreach ($reports as $report) {
      $options[$report['id']] = $report['name'];
    }
    if (empty($saved_options)) {
      $saved_options = [];
    }
    $form['reports'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Site Reports'),
      '#description' => $this->t('Check the box to run any reports on the audit page. If no reports are selected then all reports will be run.'),
      '#options' => $options,
      '#default_value' => $saved_options,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selected_options = $form_state->getValue('reports');
    $this->config('site_audit.settings')->set('reports', $selected_options)->save();
    parent::submitForm($form, $form_state);
  }

}
