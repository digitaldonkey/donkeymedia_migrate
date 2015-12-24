<?php
namespace Drupal\donkeymedia_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Description of MixingbowlRating
 *
 * @MigrateProcessPlugin(
 *   id = "donkeymedia_p_alias"
 * )
 *
 * @author mike
 */
class MixingbowlPAlias extends ProcessPluginBase {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // expect $value to be a url with query parameters (?p=123)
    $parsed = parse_url($value);
    if($parsed === false || empty($parsed['query'])) {
      return $value;
    } else {
      return '/?' . $parsed['query'];
    }
  }

}
