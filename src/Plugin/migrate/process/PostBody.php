<?php

namespace Drupal\mixingbowl_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\mixingbowl_migrate\Plugin\migrate\process\MixingbowlFeaturedImageFinder;


/**
 * This plugin sets missing values on the destination.
 *
 * @MigrateProcessPlugin(
 *   id = "post_body"
 * )
 */
class PostBody extends ProcessPluginBase {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = $this->transformImages($value, $migrate_executable, $row, $destination_property);
    $value = $this->restoreCaptions($value, $migrate_executable, $row, $destination_property);
    $value = $this->transformIngredients($value, $migrate_executable, $row, $destination_property);
    $value = $this->stripRatingStars($value, $migrate_executable, $row, $destination_property);
    $value = $this->rebuildSearchLinks($value, $migrate_executable, $row, $destination_property);
    $value = $this->rewriteOldAbsoluteURLs($value);
    $value = $this->tagWhitespace($value, $migrate_executable, $row, $destination_property);
    
    return $value;
  }
  
  protected function transformImages($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // replace all images with entity-embedded responsive images from drupal file entities
    $replace = preg_replace_callback(MixingbowlFeaturedImageFinder::IMAGE_REGEX, function ($matches) {
      // find the image's UUID
      $db_row = MixingbowlFeaturedImageFinder::findFileEntityFromUri('public://' . $matches[1]);
      if($db_row !== false) {
        $uuid = $db_row['uuid'];
        $fid = $db_row['fid'];
        
        $replace_template = <<<TEMPLATE
<drupal-entity alt="${matches[2]}" data-embed-button="inline_image" data-entity-embed-display="image:responsive_image" data-entity-embed-settings="{&quot;responsive_image_style&quot;:&quot;inline_content_image&quot;,&quot;image_link&quot;:&quot;file&quot;}" data-entity-id="$fid" data-entity-label="Inline Image" data-entity-type="file" data-entity-uuid="$uuid" title=""></drupal-entity>
        
TEMPLATE;
        return $replace_template;
      } else {
        return $matches[0];
      }
    }, $value);
    
    // second markup style of images
    $replace = preg_replace_callback(MixingbowlFeaturedImageFinder::IMAGE_REGEX_2, function ($matches) {
      // this match finds actual src attributes, which are thumbnails. Strip the thumbnail part.
      $matches[1] = preg_replace('|-\d+x\d+(\.\w{2,4}$)|', '$1', $matches[1]);
      
      // find the image's UUID
      $db_row = MixingbowlFeaturedImageFinder::findFileEntityFromUri('public://' . $matches[1]);
      if($db_row !== false) {
        $uuid = $db_row['uuid'];
        $fid = $db_row['fid'];
        
        $replace_template = <<<TEMPLATE
<drupal-entity alt="${matches[2]}" data-embed-button="inline_image" data-entity-embed-display="image:responsive_image" data-entity-embed-settings="{&quot;responsive_image_style&quot;:&quot;inline_content_image&quot;,&quot;image_link&quot;:&quot;file&quot;}" data-entity-id="$fid" data-entity-label="Inline Image" data-entity-type="file" data-entity-uuid="$uuid" title=""></drupal-entity>
        
TEMPLATE;
        return $replace_template;
      } else {
        return $matches[0];
      }
    }, $replace);

    return $replace;    
  }
  
  protected function restoreCaptions($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return preg_replace_callback('|\[caption [^\]]+]<drupal-entity ([^>]+></drupal-entity>)\s?(.+?)\[/caption]|', function ($matches) {
      return '<drupal-entity data-caption="' . htmlspecialchars($matches[2]) . '" ' . $matches[1];
    },
    $value);
  }
  
  protected function transformIngredients($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // WP uses newlines and transforms to <br>s at render time, but Drupal WYSIWYG adds <p>s on [enter].
    // Solution to get desired spacing of ingredients (1/line) is to make them css-styled <li>s.
    // Thus, locate each ingredient in WP text and transform to list item.
    if($ingredients_ix = strpos($value, 'Ingredients:')) {
      $intro = substr($value, 0, $ingredients_ix);
      $rest_lines = explode("\n", substr($value, $ingredients_ix));
      
      /* Ingredients lists are charactarized by 4 or more adjacent lines of average length <= 50,
       * with at most one outlier of > 75, all terminated by a blank line.
       */
      $lengths = [];
      $three_before_had_content = false;
      $two_before_had_content = false;
      $last_had_content = false;
      $list_start_ix = false;
      for($i = 1; $i <= 55 && $list_start_ix === false; $i++) {
        $lengths[$i] = strlen($rest_lines[$i]);
        if($lengths[$i] > 75) {
          $lengths[$i] = 25;
          $outliers[$i] = 1;
        } else {
          $outliers[$i] = 0;
        }
        if(trim($rest_lines[$i]) != '') {
          if($i >= 3) {
            $avg = ($lengths[$i] + $lengths[$i - 1] + $lengths[$i - 2] + $lengths[$i - 3]) / 4;
            $outliers_count = $outliers[$i] + $outliers[$i-1] + $outliers[$i-2] + $outliers[$i-3];
          } else {
            $avg = 0;
          }
          
          if($last_had_content && $two_before_had_content && $three_before_had_content && $outliers_count <= 1 && $avg > 0 && $avg <= 50) {
            // we've found the start of the ingredients, two lines before here.
            $list_start_ix = $i - 3;
          }
          $three_before_had_content = $two_before_had_content;
          $two_before_had_content = $last_had_content;
          $last_had_content = true;
        } else {
          $three_before_had_content = $two_before_had_content;
          $two_before_had_content = $last_had_content;
          $last_had_content = false;
        }
      }
      
      if($list_start_ix !== false) {
        // now find the end, first blank line
        $list_end_ix = false;
        for($i = $list_start_ix + 1; $i < count($rest_lines) && $list_end_ix === false; $i++) {
          if(trim($rest_lines[$i]) == '') {
            $list_end_ix = $i - 1;
          }
        }
        
        if($list_end_ix === false) {
          $list_end_ix = $i;
        }
        
        // add <ul></ul> around the list
        array_splice($rest_lines, $list_start_ix, 0, array('<ul>'));
        $list_start_ix++;
        $list_end_ix++;
        array_splice($rest_lines, $list_end_ix + 1, 0, array('</ul>'));
        
        // finally, wrap each ingredient in <li>
        for($i = $list_start_ix; $i <= $list_end_ix; $i++) {
          $rest_lines[$i] = '<li>' . $rest_lines[$i] . '</li>';
        }
        
        return $intro . implode("\n", $rest_lines);
      } else {
        trigger_error($row->getSource()['title'] . ": ingredients list not found under 'Ingredients:'", E_USER_WARNING);
        return $value;
      }
    } else {
      trigger_error($row->getSource()['title'] . ": has no ingredients", E_USER_WARNING);
      return $value;
    }
  }
  
  protected function stripRatingStars($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return preg_replace('|\n([★]+)([½]*).*?\n|', '', $value);
  }
  
  protected function rebuildSearchLinks($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return preg_replace('|http://www.mymixingbowl.com/\?s=([^&"]+)|', '/search/node?keys=$1', $value);
  }
  
  protected function rewriteOldAbsoluteURLs($value) {
    return str_replace('href="http://www.mymixingbowl.com/', 'href="/', $value);
  }
  
  protected function tagWhitespace($value) {
    // wordpress raw post bodies include newline-formatted text, which are replaced with
    // <br>s at WP render time. As Drupal 8's default text formats/WYSIWYG inserts
    // <p>s into the raw body, replace double-newlines with paragraphs and single newlines
    // with <brs>
    if(substr($value, 0, 1) != '<'){
      $value = "\n\n" . $value;
    }
    
    // paragraphs
    $count = 1;
    while($count == 1) {
      $value = preg_replace('/(?:\r?\n){2,}(?!<)(.+?)(?:\r?\n|$){2,}/s', "\n" . '<p>$1</p>' . "\n\n", $value, 1, $count);
    }
    
    // newlines
    $count = 1;
    while($count == 1) {
      $value = preg_replace('/(?:\r?\n){1}(?!<)(.+?)(?:\r?\n){1}(?!<)/', "\n" . '<br />$1' . "\n", $value, 1, $count);
    }
    
    return $value;
  }
}
