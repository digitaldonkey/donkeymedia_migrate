<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\DefaultValue.
 */

namespace Drupal\donkeymedia_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\donkeymedia_migrate\Wordpress\WordpressTagsParser;

/**
 * This plugin sets missing values on the destination.
 *
 * @MigrateProcessPlugin(
 *   id = "donkeymedia_process_php"
 * )
 */
class DonkeymediaProcessPhp extends ProcessPluginBase {

  /**
   * Return content striped of Tags defined in WordpressTagsParser.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = FALSE;
    $allowed_languages = array(
      'js',
      'php',
      'html',
      'css',
      'javascript',
    );

    if (!strpos($value,'<?php')) {
      $return = $value;
    }
    else {
      $matches = array();
      preg_match_all('/<\?php(.+?)\?>/is', $value, $matches);
      $php_strings = $matches[0];
      $php_contents = $matches[1];

      foreach ($php_contents as $ind => $php) {

        // Parse codeIt() from Wordpress.
        if (strpos($php, 'codeIt')) {
          $php_return = $this->getPhpFunction($php_contents[$ind]);
          if (!is_null($php_return)) {
            foreach ($php_return as $item) {
              if (in_array($item['arg2'], $allowed_languages)) {
                $replace = $this->codeIt($item['arg1'], $item['arg2']);
              }
              else {
                trigger_error("Missing Language in CodeIt() \$item: ", E_USER_WARNING);
                var_dump($item);
              }
              $value = str_replace($value, $php_strings[$ind], $replace);
            }
          }
        }
      }
      $return = $value;
    }
    return $return;
  }


  private function getPhpFunction($str) {
    $str = str_replace("\'", "''", $str);
    $str = str_replace('\"', '"', $str);
    // See: https://regex101.com/r/eP3uU6/5 .
    $pattern = '/codeIt\s*\((?\'anf\'[\'"])(?\'arg1\'(?(?!\1).)*)\g\'anf\'\s*,\s*(?\'anf2\'[\'"])(?\'arg2\'(?(?!\3).)*)\g\'anf2\'\s*\)\s*;\s*/s';
    $return = array();
    $matches = array();
    preg_match_all($pattern, $str, $matches);
    foreach ($matches[0] as $id => $val) {
      $return[$id]['arg1'] = $matches['arg1'][$id];
      if (!empty($matches['arg2'][$id])) {
        $return[$id]['arg2'] = $matches['arg2'][$id];
      }
    }
    $return = empty($return) ? NULL : $return;
    return $return;
  }

  /**
   * Highlight code.
   */
  private function codeIt($text, $brush="html"){
    return '<code type="' . $brush . '">' . "\n" . htmlentities($text) . "\n" . '</code>' . "\n";
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }

}
