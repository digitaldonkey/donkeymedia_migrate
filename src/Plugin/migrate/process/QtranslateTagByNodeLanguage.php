<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\DefaultValue.
 */

namespace Drupal\donkeymedia_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;


/**
 * This plugin sets missing values on the destination.
 *
 * @MigrateProcessPlugin(
 *   id = "qtranslate_tag_by_node_language"
 * )
 */
class QtranslateTagByNodeLanguage extends ProcessPluginBase {

/**
 * Parse value according to Qtranslate.
 *
 * Uses Code from wordpress qtranslate plugin.
 *
 * @return array
 *   e.g. array('en')=>'Conetent','de')=>'Conetent')
 */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    module_load_include('inc', 'donkeymedia_migrate', 'qtranslate_parser');

    $result = '';
    $quicktag_support = TRUE;

    // These are defined in migration.yml.
    $available_languages = $row->getSource()['constants']['available_languages'];
    $default_language = $row->getDestination()['langcode'];

    $contents = qtrans_split($value, $available_languages);

//    foreach ($available_languages as $lang) {
//      if (isset($contents[$lang]) && strlen(trim($contents[$lang])) > 1) {
//        $result[$lang] = $contents[$lang];
//      }
//    }
    if (isset($contents[$default_language]) && strlen(trim($contents[$default_language])) > 1) {
      $result = $contents[$default_language];
    }
    return $result;
  }





  /**
   * Old version with default Language
   */
  //  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  //
  //    $lang = $row->getDestinationProperty('langcode');
  //
  //    //init vars
  //    $split_regex = "#(<!--[^-]*-->|\[:[a-z]{2}\])#ism";
  //    $current_language = "";
  //    $result = array();
  //    $result[$lang] = '';
  //
  //
  //    // Code from Qtranslate.
  //    // split text at all xml comments
  //    $blocks = preg_split($split_regex, $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
  //
  //    foreach ($blocks as $block) {
  //      // Detect language tags.
  //      if (preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches)) {
  //        if ($lang === $matches[1]) {
  //          $current_language = $matches[1];
  //        }
  //        else {
  //          $current_language = "invalid";
  //        }
  //        continue;
  //        // Detect quicktags.
  //      }
  //      elseif (preg_match("#^\[:([a-z]{2})\]$#ism", $block, $matches)) {
  //        if ($lang === $matches[1]) {
  //          $current_language = $matches[1];
  //        }
  //        else {
  //          $current_language = "invalid";
  //        }
  //        continue;
  //        // Detect ending tags.
  //      }
  //      elseif (preg_match("#^<!--:-->$#ism", $block, $matches)) {
  //        $current_language = "";
  //        continue;
  //        // Detect defective more tag.
  //      }
  //      elseif (preg_match("#^<!--more-->$#ism", $block, $matches)) {
  ////        foreach ($q_config['enabled_languages'] as $language) {
  ////          $result[$language] .= $block;
  ////        }
  //        $result[$lang] .= $block;
  //        continue;
  //      }
  //      // Correctly categorize text block.
  //      if ($current_language == "") {
  //        // General block, add to all languages.
  ////        foreach ($q_config['enabled_languages'] as $language) {
  ////          $result[$language] .= $block;
  ////        }
  //        $result[$lang] .= $block;
  //      }
  //      elseif ($current_language != "invalid") {
  //        // Specific block, only add to active language.
  //        $result[$current_language] .= $block;
  //      }
  //    }
  ////    foreach ($result as $lang => $lang_content) {
  ////      $result[$lang] = preg_replace("#(<!--more-->|<!--nextpage-->)+$#ism", "", $lang_content);
  ////    }
  //      $result[$lang] = preg_replace("#(<!--more-->|<!--nextpage-->)+$#ism", "", $result[$lang]);
  //
  //    // return $result;
  //
  //    return $result[$lang];
//  }

}
