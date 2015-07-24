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
    $eval1 = <<<EOT
\$termStore = \\Drupal::entityManager()->getStorage('taxonomy_term');
\$term = \$termStore->create(array(
  'name' => 'siteaudit',
  'description' => 'siteaudit rocks',
  'format' => filter_fallback_format(),
  'weight' => 5,
  'langcode' => 'und',
  'vid' => 'taxonomy_term',
  'parent' => array(0),
));
\$term->save();
EOT;
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
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckContentContentEntityTypesUnused->score);
  }

  /**
   * Pass if no unused content entity type.
   */
  public function testContentContentEntityTypesUnusedPass() {
    $this->drush('pm-enable', array('node'), $this->options);
    $this->drush('pm-enable', array('taxonomy'), $this->options);
    // Create a node of content type content.
    $eval1 = <<<EOT
\$nodeStore = \\Drupal::entityManager()->getStorage('node');
\$node = \$nodeStore->create(array(
  'nid' => NULL,
  'type' => 'node',
  'title' => 'Site Audit',
  'uid' => 1,
  'revision' => 0,
  'status' => TRUE,
  'promote' => 0,
  'created' => 1,
  'langcode' => 'und',
));
\$node->save();
EOT;

    // Create a taxonomy term of vocabulary taxonomy_term.
    $eval1 .= <<<EOT
\$termStore = \\Drupal::entityManager()->getStorage('taxonomy_term');
\$term = \$termStore->create(array(
  'name' => 'siteaudit',
  'description' => 'siteaudit rocks',
  'format' => filter_fallback_format(),
  'weight' => 5,
  'langcode' => 'und',
  'vid' => 'taxonomy_term',
  'parent' => array(0),
));
\$term->save();
EOT;
    $this->drush('php-eval', array($eval1), $this->options);
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
    $eval1 = <<<EOT
\$nodeStore = \\Drupal::entityManager()->getStorage('node');
\$node = \$nodeStore->create(array(
  'nid' => NULL,
  'type' => 'node',
  'title' => 'Site Audit',
  'uid' => 1,
  'revision' => 0,
  'status' => TRUE,
  'promote' => 0,
  'created' => 1,
  'langcode' => 'und',
));
\$node->save();
EOT;
    $eval1 .= <<<EOT
\$nodeStore = \\Drupal::entityManager()->getStorage('node');
\$node = \$nodeStore->create(array(
  'nid' => NULL,
  'type' => 'node',
  'title' => 'Site Audit 1',
  'uid' => 1,
  'revision' => 0,
  'status' => TRUE,
  'promote' => 0,
  'created' => 1,
  'langcode' => 'und',
));
\$node->save();
EOT;
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
    $eval1 = <<<EOT
\$nodeStore = \\Drupal::entityManager()->getStorage('node');
\$node = \$nodeStore->create(array(
  'nid' => NULL,
  'type' => 'node',
  'title' => 'Site Audit',
  'uid' => 1,
  'revision' => 0,
  'status' => TRUE,
  'promote' => 0,
  'created' => 1,
  'langcode' => 'und',
));
\$node->save();
EOT;

    $eval1 .= <<<EOT
\$nodeStore = \\Drupal::entityManager()->getStorage('node');
\$node = \$nodeStore->create(array(
  'nid' => NULL,
  'type' => 'node',
  'title' => 'Site Audit',
  'uid' => 1,
  'revision' => 0,
  'status' => TRUE,
  'promote' => 0,
  'created' => 1,
  'langcode' => 'und',
));
\$node->save();
EOT;
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-content', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckContentDuplicateTitles->score);
  }

}
