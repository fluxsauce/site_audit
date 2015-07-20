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

  /**
   * Warn if a content entity type is unused.
   */
  public function testContentContentEntityTypesUnusedWarn() {
    $this->drush('pm-enable', array('taxonomy'), $this->options);
    $this->drush('audit-content', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = $this->getOutput();
    print $output;
    $output = json_decode($output);
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckContentContentEntityTypesUnused->score);
  }

  /**
   * Pass if no unused content entity type.
   */
  public function testContentContentEntityTypesUnusedPass() {
    $this->drush('pm-enable', array('node'), $this->options);
    $this->drush('pm-enable', array('taxonomy'), $this->options);
    // Create a node of content type content.
    $eval1 = "\$nodeStore = \\Drupal::entityManager()->getStorage('node');";
    $eval1 .= "\$node = \$nodeStore->create(array(";
    $eval1 .= "'nid' => NULL,";
    $eval1 .= "'type' => 'node',";
    $eval1 .= "'title' => 'Site Audit',";
    $eval1 .= "'uid' => 1,";
    $eval1 .= "'revision' => 0,";
    $eval1 .= "'status' => TRUE,";
    $eval1 .= "'promote' => 0,";
    $eval1 .= "'created' => 1,";
    $eval1 .= "'langcode' => 'und',";
    $eval1 .= "));";
    $eval1 .= "\$node->save();";

    // Create a taxonomy term of vocabulary taxonomy_term.
    $eval2 = "\$termStore = \\Drupal::entityManager()->getStorage('taxonomy_term');";
    $eval2 .= "\$term = \$termStore->create(array(";
    $eval2 .= "'name' => 'siteaudit',";
    $eval2 .= "'description' => 'siteaudit rocks',";
    $eval2 .= "'format' => filter_fallback_format(),";
    $eval2 .= "'weight' => 5,";
    $eval2 .= "'langcode' => 'und',";
    $eval2 .= "'vid' => 'taxonomy_term',";
    $eval2 .= "'parent' => array(0),";
    $eval2 .= "));";
    $eval2 .= "\$term->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('php-eval', array($eval2), $this->options);
    $this->drush('audit-content', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckContentContentEntityTypesUnused->score);
  }

  /**
   * Pass if no nodes with duplicate titles.
   */
  public function testContentDuplicateTitlesPass() {
    $this->drush('pm-enable', array('node'), $this->options);
    $eval1 = "\$nodeStore = \\Drupal::entityManager()->getStorage('node');";
    $eval1 .= "\$node = \$nodeStore->create(array(";
    $eval1 .= "'nid' => NULL,";
    $eval1 .= "'type' => 'node',";
    $eval1 .= "'title' => 'Site Audit',";
    $eval1 .= "'uid' => 1,";
    $eval1 .= "'revision' => 0,";
    $eval1 .= "'status' => TRUE,";
    $eval1 .= "'promote' => 0,";
    $eval1 .= "'created' => 1,";
    $eval1 .= "'langcode' => 'und',";
    $eval1 .= "));";
    $eval1 .= "\$node->save();";

    $eval1 .= "\$node = \$nodeStore->create(array(";
    $eval1 .= "'nid' => NULL,";
    $eval1 .= "'type' => 'node',";
    $eval1 .= "'title' => 'Site Audit 1',";
    $eval1 .= "'uid' => 1,";
    $eval1 .= "'revision' => 0,";
    $eval1 .= "'status' => TRUE,";
    $eval1 .= "'promote' => 0,";
    $eval1 .= "'created' => 1,";
    $eval1 .= "'langcode' => 'und',";
    $eval1 .= "));";
    $eval1 .= "\$node->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-content', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckContentDuplicateTitles->score);
  }
  /**
   * Warn if nodes with duplicate titles present.
   */
  public function testContentDuplicateTitlesWarn() {
    $this->drush('pm-enable', array('node'), $this->options);
    $eval1 = "\$nodeStore = \\Drupal::entityManager()->getStorage('node');";
    $eval1 .= "\$node = \$nodeStore->create(array(";
    $eval1 .= "'nid' => NULL,";
    $eval1 .= "'type' => 'node',";
    $eval1 .= "'title' => 'Site Audit',";
    $eval1 .= "'uid' => 1,";
    $eval1 .= "'revision' => 0,";
    $eval1 .= "'status' => TRUE,";
    $eval1 .= "'promote' => 0,";
    $eval1 .= "'created' => 1,";
    $eval1 .= "'langcode' => 'und',";
    $eval1 .= "));";
    $eval1 .= "\$node->save();";

    $eval1 .= "\$node = \$nodeStore->create(array(";
    $eval1 .= "'nid' => NULL,";
    $eval1 .= "'type' => 'node',";
    $eval1 .= "'title' => 'Site Audit',";
    $eval1 .= "'uid' => 1,";
    $eval1 .= "'revision' => 0,";
    $eval1 .= "'status' => TRUE,";
    $eval1 .= "'promote' => 0,";
    $eval1 .= "'created' => 1,";
    $eval1 .= "'langcode' => 'und',";
    $eval1 .= "));";
    $eval1 .= "\$node->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-content', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckContentDuplicateTitles->score);
  }

}
