<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\FieldCount.
 */

/**
 * Class SiteAuditCheckContentFieldCount.
 */
class SiteAuditCheckContentFieldCount extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Field counts');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Total number of fields');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('There are no fields available!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    $ret_val = dt('There are @count total fields.', array(
      '@count' => count($this->registry['fields']),
    ));
    if (drush_get_option('detail')) {
      if (drush_get_option('html')) {
        $ret_val = '<p>' . $ret_val . '</p>';
        $ret_val .= '<table class="table table-condensed">';
        $ret_val .= '<tr><th>' . dt('Name') . '</th><th>' . dt('Type') . '</th></tr>';
        foreach ($this->registry['fields'] as $field_name => $description) {
          $ret_val .= "<tr><td>$field_name</td><td>$description</td></tr>";
        }
        $ret_val .= '</table>';
      }
      else {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= dt('Name: Type') . PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= '----------';
        foreach ($this->registry['fields'] as $field_name => $description) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
          $ret_val .= "$field_name: $description";
        }
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('There are @count total fields, which is higher than average', array(
      '@count' => count($this->registry['fields']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Consider disabling the field module.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $map = \Drupal::entityManager()->getFieldMap();
    $this->registry['fields'] = array();
    $this->registry['default_fields'] = array(
      'body',
      'comment_body',
    );
    foreach ($map as $entity => $fields) {
      foreach ($fields as $field => $description) {
        if (preg_match('/^field\_/', $field) || in_array($field, $this->registry['default_fields'])) {
          $this->registry['fields'][$field] = $description['type'];
        }
      }
    }
    if (count($this->registry['fields']) == 0) {
      $this->abort;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    elseif (count($this->registry['fields']) > 75) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
