<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\mixingbowl_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Description of MixingbowlFeaturedImageFinder
 * 
 * @MigrateProcessPlugin(
 *   id = "mixingbowl_featured_image_finder"
 * )
 * 
 * @author mike
 */
class MixingbowlFeaturedImageFinder extends ProcessPluginBase {
  
  const IMAGE_REGEX = '|<a href="http://www.mymixingbowl.com/wp-content/uploads/([^"]+)"><img .+ alt="([^"]*)".+/></a>|';
  const IMAGE_REGEX_2 = '|<a href="http://www.mymixingbowl.com/\?attachment_id=[^"]+".*?><img .+ src="http://www.mymixingbowl.com/wp-content/uploads/([^"]+)".*?alt="([^"]*)".+/></a>|';
  
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $image_pulls = self::imagesFromBody($value);
    if(!empty($image_pulls)) {
      $featured_image_info = $image_pulls[count($image_pulls) - 1];
      if(strpos($destination_property, '/alt')) {
        return $featured_image_info[2];
      } 
      else {
        $file_entity = self::findFileEntityFromUri('public://' . $featured_image_info[1]);
        if($file_entity === false) {
          throw new MigrateSkipRowException();
        }
        return $file_entity['fid'];
      }
    } else {
      return null;
    }
  }
  
  public static function imagesFromBody($body) {
    $matches = array();
    preg_match_all(self::IMAGE_REGEX, $body, $matches, PREG_SET_ORDER);
    
    $matches2 = array();
    preg_match_all(self::IMAGE_REGEX_2, $body, $matches2, PREG_SET_ORDER);
    foreach($matches2 as &$instance) {
      // this match finds actual src attributes, which are thumbnails. Strip the thumbnail part.
      $instance[1] = preg_replace('|-\d+x\d+(\.\w{2,4}$)|', '$1', $instance[1]);
    }
    
    return array_merge($matches, $matches2);
  }
  
  public static function findFileEntityFromUri($uri) {
      $uuid_qry = db_select('file_managed', 'fm')
              ->fields('fm', array('fid', 'uuid'))
              ->condition('uri', $uri)
              ->execute();
      $db_row = $uuid_qry->fetchAssoc();
      
      if($db_row === false) {
        trigger_error("Missing file for $uri", E_USER_WARNING);
      }
      
      return $db_row;
  }
}
