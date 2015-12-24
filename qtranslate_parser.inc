<?php

// splits text with language tags into array
function qtrans_split($text, $available_languages = array('en', 'de'), $quicktags = TRUE) {

  $q_config['enabled_languages'] = $available_languages;

  // Init vars.
  $split_regex = "#(<!--[^-]*-->|\[:[a-z]{2}\])#ism";
  $current_language = "";
  $result = array();
  foreach ($q_config['enabled_languages'] as $language) {
    $result[$language] = "";
  }

  // Split text at all xml comments.
  $blocks = preg_split($split_regex, $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
  foreach ($blocks as $block) {
    // Detect language tags.
    if (preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches)) {
      if (in_array($matches[1], $available_languages)) {
        $current_language = $matches[1];
      }
      else {
        $current_language = "invalid";
      }
      continue;
      // Detect quicktags.
    }
    elseif ($quicktags && preg_match("#^\[:([a-z]{2})\]$#ism", $block, $matches)) {
      if (in_array($matches[1], $available_languages)) {
        $current_language = $matches[1];
      }
      else {
        $current_language = "invalid";
      }
      continue;
      // Detect ending tags.
    }
    elseif (preg_match("#^<!--:-->$#ism", $block, $matches)) {
      $current_language = "";
      continue;
      // Detect defective more tag.
    }
    elseif (preg_match("#^<!--more-->$#ism", $block, $matches)) {
      foreach ($q_config['enabled_languages'] as $language) {
        $result[$language] .= $block;
      }
      continue;
    }
    // Correctly categorize text block.
    if ($current_language == "") {
      // General block, add to all languages.
      foreach ($q_config['enabled_languages'] as $language) {
        $result[$language] .= $block;
      }
    }
    elseif ($current_language != "invalid") {
      // Specific block, only add to active language.
      $result[$current_language] .= $block;
    }
  }
  foreach ($result as $lang => $lang_content) {
    $result[$lang] = preg_replace("#(<!--more-->|<!--nextpage-->)+$#ism","",$lang_content);
  }
  return $result;
}
