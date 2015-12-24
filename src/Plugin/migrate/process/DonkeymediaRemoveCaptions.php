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
 *   id = "donkeymedia_remove_captions"
 * )
 */
class DonkeymediaRemoveCaptions extends ProcessPluginBase {

  use WordpressTagsParser;

  /**
   * Return content striped of Tags defined in WordpressTagsParser.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $this->getTags($value, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }

}
