<?php
namespace Drupal\donkeymedia_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Description of MixingbowlRating
 *
 * @MigrateProcessPlugin(
 *   id = "donkeymedia_rating"
 * )
 *
 * @author mike
 */
class MixingbowlRating extends ProcessPluginBase {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $matches = [];
    if(preg_match('|\n([★]+)([½]*).*?\n|', $value, $matches)) {
      $rating = mb_strlen($matches[1]);
      if($matches[2]) {
        $rating += .5;
      }
      return $rating;
    } else {
      return null;
    }
  }

}
