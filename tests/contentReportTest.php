<?php
/**
 * @file
 * Contains /site_audit/tests/ContentReportCase.
 */

namespace Unish;

require_once 'Abstract.php';

/**
 * Class ContentReportCase.
 *
 * @group commands
 */
class ContentReportCase extends SiteAuditTestAbstract {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $this->setUpSiteAuditTestEnvironment();
  }

  /**
   * Warn if empty vocabularies present.
   */
  public function testVocabulariesUnusedWarn() {
    $this->drush('pm-enable', array('taxonomy'), $this->options);
    $this->drush('audit-content', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckContentVocabulariesUnused->score);
  }

  /**
   * Pass if no empty vocabularies present.
   */
  public function testVocabulariesUnusedPass() {
    $this->drush('pm-enable', array('taxonomy'), $this->options);
    $eval1 = "\$termStore = \\Drupal::entityManager()->getStorage('taxonomy_term');";
    $eval1 .= "\$term = \$termStore->create(array(";
    $eval1 .= "'name' => 'siteaudit',";
    $eval1 .= "'description' => 'siteaudit rocks',";
    $eval1 .= "'format' => filter_fallback_format(),";
    $eval1 .= "'weight' => 5,";
    $eval1 .= "'langcode' => 'und',";
    $eval1 .= "'vid' => 'taxonomy_term',";
    $eval1 .= "'parent' => array(0),";
    $eval1 .= "));";
    $eval1 .= "\$term->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-content', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckContentVocabulariesUnused->score);
  }

}
