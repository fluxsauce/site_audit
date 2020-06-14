<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the SecurityMenuRouter Check.
 *
 * @SiteAuditCheck(
 *  id = "security_menu_router",
 *  name = @Translation("Menu Router"),
 *  description = @Translation("Check for potentially malicious entries in the menu router."),
 *  report = "security"
 * )
 */
class SecurityMenuRouter extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    $ret_val = $this->t('The following potentially malicious paths have been discovered: @list', [
      '@list' => implode(', ', array_keys($this->registry->menu_router)),
    ]);

    // If ($this->options['detail']) {.
    if (TRUE) {
      // If ($this->options['html']) {.
      if (TRUE) {
        $ret_val .= '<br/>';
        $ret_val .= '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>' . $thist('Path') . '</th><th>' . $this->t('Reason') . '</th></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry->menu_router as $path => $malicious_callbacks) {
          foreach ($malicious_callbacks as $malicious_callback) {
            $ret_val .= '<tr><td>' . $path . '</td><td>' . $malicious_callback . '</td></tr>';
          }
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        foreach ($this->registry->menu_router as $path => $malicious_callbacks) {
          foreach ($malicious_callbacks as $malicious_callback) {
            $ret_val .= PHP_EOL;
            if (!$this->options['json']) {
              $ret_val .= str_repeat(' ', 6);
            }
            $ret_val .= '- ' . $path . ': ' . $malicious_callback;
          }
        }
      }
    }
    return $ret_val;
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('No known potentially malicious entries were detected in the menu_router table.', []);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL) {
      return $this->t('Delete the file containing the offending menu_router entries, clear the site caches, update your Drupal site code, and check your entire codebase for questionable code using a tool like the <a href="https://www.drupal.org/project/hacked" target="_blank">Hacked!</a> module.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    // DRUPAL SA-CORE-2014-005 Exploits.
    $dangerous_callbacks = [
      'php_eval' => 'executes arbitrary PHP code',
      'assert' => 'executes arbitrary PHP code',
      'exec' => 'executes external programs',
      'passthru' => 'executes external programs and displays raw output',
      'system' => 'executes external programs and displays raw output',
      'shell_exec' => 'executes commands via shell and returns complete output',
      'popen' => 'opens process file pointer',
      'proc_open' => 'executes a command, opens file pointers for I/O',
      'pcntl_exec' => 'executes program in current process space',
      'eval' => 'evalues string as PHP code',
      'preg_replace' => 'can be used to eval() on match',
      'create_function' => 'creates anonymous functions',
      'include' => 'includes and evaluates files',
      'include_once' => 'includes and evaluates files',
      'require' => 'includes and evaluates files',
      'require_once' => 'includes and evaluates files',
      'ob_start' => 'can specify callback function',
      'array_diff_uassoc' => 'can specify callback function',
      'array_diff_ukey' => 'can specify callback function',
      'array_filter' => 'can specify callback function',
      'array_intersect_uassoc' => 'can specify callback function',
      'array_intersect_ukey' => 'can specify callback function',
      'array_map' => 'can specify callback function',
      'array_reduce' => 'can specify callback function',
      'array_udiff_assoc' => 'can specify callback function',
      'array_udiff_uassoc' => 'can specify callback function',
      'array_udiff' => 'can specify callback function',
      'array_uintersect_assoc' => 'can specify callback function',
      'array_uintersect_uassoc' => 'can specify callback function',
      'array_uintersect' => 'can specify callback function',
      'array_walk_recursive' => 'can specify callback function',
      'array_walk' => 'can specify callback function',
      'assert_options' => 'can specify callback function',
      'uasort' => 'can specify callback function',
      'uksort' => 'can specify callback function',
      'usort' => 'can specify callback function',
      'preg_replace_callback' => 'can specify callback function',
      'spl_autoload_register' => 'can specify callback function',
      'iterator_apply' => 'can specify callback function',
      'call_user_func' => 'can specify callback function',
      'call_user_func_array' => 'can specify callback function',
      'register_shutdown_function' => 'can specify callback function',
      'register_tick_function' => 'can specify callback function',
      'set_error_handler' => 'can specify callback function',
      'set_exception_handler' => 'can specify callback function',
      'session_set_save_handler' => 'can specify callback function',
      'sqlite_create_aggregate' => 'can specify callback function',
      'sqlite_create_function' => 'can specify callback function',
      'phpinfo' => 'information disclosure',
      'posix_getlogin' => 'information disclosure',
      'posix_ttyname' => 'information disclosure',
      'getenv' => 'information disclosure',
      'get_current_user' => 'information disclosure',
      'proc_get_status' => 'information disclosure',
      'get_cfg_var' => 'information disclosure',
      'disk_free_space' => 'information disclosure',
      'disk_total_space' => 'information disclosure',
      'diskfreespace' => 'information disclosure',
      'getcwd' => 'information disclosure',
      'getlastmo' => 'information disclosure',
      'getmygid' => 'information disclosure',
      'getmyinode' => 'information disclosure',
      'getmypid' => 'information disclosure',
      'getmyuid' => 'information disclosure',
      'echo' => 'information disclosure',
      'print' => 'information disclosure',
      'extract' => 'imports variables',
      'parse_str' => 'imports variables',
      'putenv' => 'sets environment variables',
      'ini_set' => 'changes configuration',
      'mail' => 'sends mail',
      'header' => 'sets headers',
      'proc_nice' => 'process management',
      'proc_terminate' => 'process management',
      'proc_close' => 'process management',
      'pfsockopen' => 'socket management',
      'fsockopen' => 'socket management',
      'apache_child_terminate' => 'process management',
      'posix_kill' => 'process management',
      'posix_mkfifo' => 'process management',
      'posix_setpgid' => 'process management',
      'posix_setsid' => 'process management',
      'posix_setuid' => 'process management',
      'fopen' => 'opens files',
      'tmpfile' => 'opens files',
      'bzopen' => 'opens files',
      'gzopen' => 'opens files',
      'SplFileObject->__construct' => 'opens files',
      'chgrp' => 'modifies files',
      'chmod' => 'modifies files',
      'chown' => 'modifies files',
      'copy' => 'modifies files',
      'file_put_contents' => 'modifies files',
      'lchgrp' => 'modifies files',
      'lchown' => 'modifies files',
      'link' => 'modifies files',
      'mkdir' => 'modifies files',
      'move_uploaded_file' => 'modifies files',
      'rename' => 'modifies files',
      'rmdir' => 'modifies files',
      'symlink' => 'modifies files',
      'tempnam' => 'modifies files',
      'touch' => 'modifies files',
      'unlink' => 'modifies files',
      'imagepng' => 'modifies files',
      'imagewbmp' => 'modifies files',
      'image2wbmp' => 'modifies files',
      'imagejpeg' => 'modifies files',
      'imagexbm' => 'modifies files',
      'imagegif' => 'modifies files',
      'imagegd' => 'modifies files',
      'imagegd2' => 'modifies files',
      'iptcembed' => 'modifies files',
      'ftp_get' => 'modifies files',
      'ftp_nb_get' => 'modifies files',
      'file_exists' => 'reads files',
      'file_get_contents' => 'reads files',
      'file' => 'reads files',
      'fileatime' => 'reads files',
      'filectime' => 'reads files',
      'filegroup' => 'reads files',
      'fileinode' => 'reads files',
      'filemtime' => 'reads files',
      'fileowner' => 'reads files',
      'fileperms' => 'reads files',
      'filesize' => 'reads files',
      'filetype' => 'reads files',
      'glob' => 'reads files',
      'is_dir' => 'reads files',
      'is_executable' => 'reads files',
      'is_file' => 'reads files',
      'is_link' => 'reads files',
      'is_readable' => 'reads files',
      'is_uploaded_file' => 'reads files',
      'is_writable' => 'reads files',
      'is_writeable' => 'reads files',
      'linkinfo' => 'reads files',
      'lstat' => 'reads files',
      'parse_ini_file' => 'reads files',
      'pathinfo' => 'reads files',
      'readfile' => 'reads files',
      'readlink' => 'reads files',
      'realpath' => 'reads files',
      'stat' => 'reads files',
      'gzfile' => 'reads files',
      'readgzfile' => 'reads files',
      'getimagesize' => 'reads files',
      'imagecreatefromgif' => 'reads files',
      'imagecreatefromjpeg' => 'reads files',
      'imagecreatefrompng' => 'reads files',
      'imagecreatefromwbmp' => 'reads files',
      'imagecreatefromxbm' => 'reads files',
      'imagecreatefromxpm' => 'reads files',
      'ftp_put' => 'reads files',
      'ftp_nb_put' => 'reads files',
      'exif_read_data' => 'reads files',
      'read_exif_data' => 'reads files',
      'exif_thumbnail' => 'reads files',
      'exif_imagetype' => 'reads files',
      'hash_file' => 'reads files',
      'hash_hmac_file' => 'reads files',
      'hash_update_file' => 'reads files',
      'md5_file' => 'reads files',
      'sha1_file' => 'reads files',
      'highlight_file' => 'reads files',
      'show_source' => 'reads files',
      'php_strip_whitespace' => 'reads files',
      'get_meta_tags' => 'reads files',
    ];
    $all_routes = \Drupal::service('router.route_provider')->getAllRoutes();
    $callback_keys = [
      '_controller',
      '_title_callback',
    ];
    foreach ($all_routes as $route) {
      $defaults = $route->getDefaults();
      foreach ($callback_keys as $key) {
        if (isset($defaults[$key]) && in_array($defaults[$key], array_keys($dangerous_callbacks))) {
          $callback = $defaults[$key];
          $parameters = (new \ReflectionFunction($callback))->getParameters();
          $parameters = array_map(function ($parameter) {
            return $parameter->name;
          }, $parameters);
          $passed_arguments = array_intersect($parameters, array_keys($defaults));
          $arguments = [];
          foreach ($passed_arguments as $argument) {
            $arguments[] = $argument . '=' . $defaults[$argument];
          }
          $this->registry->menu_router[$route->getPath()][] = $key . ' "' . $callback . '" (' . $dangerous_callbacks[$callback] . ') with the following arguments: "' . implode(',', $arguments) . '"';
        }
      }
    }
    if (empty($this->registry->menu_router)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}
