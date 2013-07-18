<?php

class AuditCheckWatchdogCount extends AuditCheck {
  public function getLabel() {
    return dt('Count');
  }

  public function getResultFail() {}

  public function getResultInfo() {
    if (!$this->registry['count_entries']) {
      return dt('There are no dblog entries.');
    }
    return dt('There are @count_entries log entries.', array(
      '@count_entries' => $this->registry['count_entries'],
    ));
  }

  public function getResultPass() {}

  public function getResultWarning() {}

  public function getAction() {}

  public function getDescription() {
    return dt('Number of dblog entries.');
  }

  public function getScore() {
    $sql_query  = 'SELECT COUNT(wid) ';
    $sql_query .= 'FROM {watchdog} ';
    $this->registry['count_entries'] = db_query($sql_query)->fetchField();
    if (!$this->registry['count_entries']) {
      $this->abort = TRUE;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
