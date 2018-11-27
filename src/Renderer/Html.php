<?php

namespace Drupal\site_audit\Renderer;

use Drupal\site_audit\Renderer;
use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\site_audit\Plugin\SiteAuditReportBase;
use Drupal\Core\Render\Renderer AS CoreRenderer;

class Html extends Renderer {

  /**
   * The build array for the page
   */
  var $build;

  /**
   * @inherit
   */
  public function __construct($report, $logger, $options, $output) {
    parent::__construct($report, $logger, $options, $output);
    $this->buildHeader();
  }

  /**
   * Get the CSS class associated with a percentage.
   *
   * @return string
   *   Twitter Bootstrap CSS class.
   */
  public function getPercentCssClass($percent) {
    if ($percent > 80) {
      return 'success';
    }
    if ($percent > 65) {
      return 'error';
    }
    if ($percent >= 0) {
      return 'caution';
    }
    return 'info';
  }

  /**
   * Get the CSS class associated with a score.
   *
   * @return string
   *   Name of the Twitter bootstrap class.
   */
  public function getScoreCssClass($score = NULL) {
    switch ($score) {
      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS:
        return 'success';

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN:
        return 'warning';

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO:
        return 'info';

      default:
        return 'danger';

    }
  }

  /**
   * Build the header of the page
   */
  public function buildHeader() {
    $this->build = [
      #'#type' => 'page',
      'container' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => 'container'
        ],
        'page_header' => [
          '#type' => 'html_tag',
          '#tag' => 'h2',
          '#value' => $this->t('<a href="@site-audit-uri">Site Audit</a> report for @site', [
            '@site-audit-uri' => 'https://drupal.org/project/site_audit',
            '@site' => $this->options['uri'],
          ]),
          '#attributes' => [
            'id' => 'page-header',
          ],
          'br' => [
            '#type' => 'html_tag',
            '#tag' => 'br',
          ],
          'sub_head' => [
            '#type' => 'html_tag',
            '#tag' => 'small',
            '#value' => $this->t('Generated on @date_time' , ['@date_time' => \Drupal::service('date.formatter')->format(REQUEST_TIME)]),
          ]
        ],
      ],
    ];
    if (is_array($this->report)) {
      // there are multiple reports
      $this->build['container']['summary'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => 'summary',
        ],
      ];
      $this->build['container']['summary']['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Summary'),
      ];
      $this->build['container']['summary']['links'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
      ];
      foreach ($this->report as $report) {
        $this->build['container']['summary']['links'][$report->getPluginId()] = [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#value' => $report->getLabel() . ' (' . $report->getPercent() . '%)',
          '#attributes' => [
            'href' => '#' . $report->getPluginId(),
            'class' => $this->getPercentCssClass($report->getPercent()),
          ],
        ];
      }
    }
  }

  /**
   * check to see if the bootstrap option was selected and wrap in HTMl and add
   * bootstrap derived styles is so
   */
  protected function checkBootstrap() {
    if ($this->options['bootstrap']) {
      $this->build = [
        '#type' => 'html_tag',
        '#tag' => 'html',
        'head' => [
          '#type' => 'html_tag',
          '#tag' => 'head',
          'title' => [
            '#type' => 'html_tag',
            '#tag' => 'title',
            '#value' => $this->t('Site Audit report for @site', [
              '@site' => $this->options['uri'],
            ]),
          ],
          'bootstrap' => [
            '#type' => 'html_tag',
            '#tag' => 'link',
            '#attributes' => [
              'href' => 'https://stackpath.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css',
              'rel' => 'stylesheet',
              'crossorigin' => 'anonymous',
            ],
          ],
          'styles' => [
            '#type' => 'html_tag',
            '#tag' => 'style',
            '#value' => $this->getStyles(),
            '#attributes' => [
              'type' => 'text/css',
            ]
          ]
        ],
        'body' => [
          '#type' => 'html_tag',
          '#tag' => 'body',
          $this->build,
        ],
      ];
    }
  }

  /**
   * render either one report, or multiple
   */
  public function render($detail = FALSE) {
    if (is_array($this->report)) {
      // there are multiple reports
      foreach ($this->report AS $report) {
        $this->build['container'][$report->getPluginId()] = $this->renderReport($report);
        $this->build['container'][$report->getPluginId()]['top_link'] = [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#value' => $this->t('Back to top'),
          '#attributes' => [
            'href' => '#summary'
          ]
        ];
      }
    }
    else {
      $this->build['container'][$this->report->getPluginId()] = $this->renderReport($this->report);
    }

    $this->checkBootstrap();

    $out = \Drupal::service('renderer')->renderRoot($this->build);
    return $out;
  }

  /**
   * Render a single report
   */
  public function renderReport($report) {
    $build = [];
    // the report header
    $build['report_label'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#value' => $report->getLabel() . ' ',
      '#attributes' => [
        'id' => $report->getPluginId(),
      ],
      'percent' => [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $report->getPercent() . '%',
        '#attributes' => [
          'class' => 'label label-'. $this->getPercentCssClass($report->getPercent()),
        ],
      ],
    ];

    $percent = $report->getPercent();

    if ($percent != SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO) {
      // show percent
      $build['report_label']['percent'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $percent . '%',
        '#prefix' => ' ',
        '#attributes' => [
          'class' => 'label label-' . $this->getPercentCssClass($percent),
        ],
      ];
    }
    else {
      $build['label']['info'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $this->t('Info'),
        '#attributes' => [
          'class' => 'label label-info',
        ],
      ];
    }

    if ($percent == 100) {
      $build['success'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => '<strong>' . $this->t('Well done!') . '</strong> ' . $this->t('No action required.'),
        '#attributes' => [
          'class' => 'text-success',
        ],
      ];
    }

    if ($this->options['detail'] || $percent != 100) {
      foreach ($report->getCheckObjects() as $check) {
        $checkBuild = [];
        $score = $check->getScore();
        if ($this->options['detail'] || $score < SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS || $percent == SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO) {
          // Heading.
          $checkBuild['panel']['panel_heading'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => '<strong>' . $check->getLabel() . '</strong>',
            '#attributes' => [
              'class' => 'panel-heading',
            ],
          ];

          if ($this->options['detail']) {
            $checkBuild['panel']['panel_heading']['description'] = [
              '#type' => 'html_tag',
              '#tag' => 'small',
              '#value' => '- ' . $check->getDescription(),
            ];
          }

          // Result.
          $checkBuild['#result'] = $check->getResult();
          if (is_array($check->getResult())) {
            $checkBuild['result'] = $check->getResult();
            if (!isset($checkBuild['result']['#attributes'])) {
              $checkBuild['result']['#attributes'] = [
                'class' => '',
              ];
            }
            $checkBuild[$result]['#attributes']['class'] = 'well result';
          }
          else {
            $checkBuild['detail'] = [
              '#type' => 'html_tag',
              '#tag' => 'p',
              '#value' => $check->getResult(),
              '#attributes' => [
                'class' => 'well result',
              ],
            ];
          }

          // Action.
          if ($action = $check->renderAction()) {
            $checkBuild['action'] = [
              '#type' => 'html_tag',
              '#tag' => 'div',
              '#attributes' => [
                'class' => 'well action',
              ],
            ];
            if (!is_array($action)) {
              $checkBuild['action']['text'] = [
                '#type' => 'html_tag',
                '#tag' => 'p',
                '#value' => $action,
              ];
            }
            else {
              $checkBuild['action']['rendered'] = $action;
            }
          }
        }
        $build[$check->getPluginId()] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          //'#value' => '<strong>' . $this->t('Well done!') . '</strong> ' . $this->t('No action required.'),
          '#attributes' => [
            'class' => 'panel panel-' . $this->getScoreCssClass($check->getScore()),
          ],
          $checkBuild,
        ];
      }
    }
    return $build;
  }

  /**
   * Render the results as a table
   */
  public function table($element) {
    return render($element);
  }

  public function escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  /**
   * provide the bootstrap derived styles
   */
  private function getStyles() {
    $styles = <<<STYLES
.panel-success .panel-heading, .btn-success, .label-success, .alert-success {color:#264926;background-color:#dff0d8;}
.text-success {color:#264926;}
.panel-warning .panel-heading, .btn-warning, .label-warning, .alert-warning {color:#000000;background-color:#fcf8e3;}
.text-warning {color:#000000;}
.panel-danger .panel-heading, .btn-danger, .label-danger, .alert-danger {color:#752e2d;background-color:#eed3d7;}
.text-danger {color:#752e2d;}
.panel-info .panel-heading, .btn-info, .label-info, .alert-info {color:#225066;background-color:#d9edf7;}
.text-info {color:#225066;}
a {color: #275c8a;}
h1 small {color: #4c4c4c;}
blockquote small {color: #4c4c4c;}
STYLES;
    return $styles;
  }
}
