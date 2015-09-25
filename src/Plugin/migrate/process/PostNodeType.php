<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\DefaultValue.
 */

namespace Drupal\mixingbowl_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;


/**
 * This plugin sets missing values on the destination.
 *
 * @MigrateProcessPlugin(
 *   id = "post_node_type"
 * )
 */
class PostNodeType extends ProcessPluginBase {

  /**
   * Posts are either type recpie or restaurant_review. 
   * Recpies contain <div class="recipe">, everything else is considered type
   * restaurant_review.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $match = preg_match('/<div\s+class=[\'"]recipe/', $row->getSource()['content:encoded']);
    if($match === false) throw new \Exception('Regex error parsing body for node type');
    return $match == 1 ? 'recipe' : 'restaurant_review';
  }
}
