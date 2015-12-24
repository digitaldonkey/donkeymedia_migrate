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
 *   id = "post_node_type_project"
 * )
 */
class PostNodeTypeProject extends ProcessPluginBase {

  /**
   * Posts are either type recpie or restaurant_review.
   * Recpies contain <div class="recipe">, everything else is considered type
   * restaurant_review.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $src = $row->getSource();

    if (isset($src['category']) && !empty($src['category'])) {
      $cats = $src['category'];
      if (is_array($cats) && in_array('References', $cats)) {
        $value = 'project';
      }
    }
    return is_string($value) ? $value : NULL;
  }

}
