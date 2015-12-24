<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\DefaultValue.
 */

namespace Drupal\donkeymedia_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * This plugin sets missing values on the destination.
 *
 * @MigrateProcessPlugin(
 *   id = "qtranslate_get_language"
 * )
 */
class QtranslateGetLanguage extends ProcessPluginBase {

  /**
   *
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    module_load_include('inc', 'donkeymedia_migrate', 'qtranslate_parser');
    $result = array();
    $available_languages = array('en', 'de');
    $default_language = 'en';

    $available_content = qtrans_split($value, $available_languages);
    foreach ($available_languages as $lang) {
      if (isset($available_content[$lang]) && strlen(trim($available_content[$lang])) > 1) {
        $result[] = $lang;
      }
    }
    return $result;
  }

}
