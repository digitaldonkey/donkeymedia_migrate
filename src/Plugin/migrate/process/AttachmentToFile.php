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
 *   id = "attachment_to_file"
 * )
 */
class AttachmentToFile extends ProcessPluginBase {

  use WordpressTagsParser;

  /**
   * Posts are either type recpie or restaurant_review.
   * Recpies contain <div class="recipe">, everything else is considered type
   * restaurant_review.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = array();

    $caption_tags = $this->getTags($value)['caption']['values'];

    if (is_array($caption_tags)) {

      foreach ($caption_tags as $image) {
        // Search for imported File:
        $q = db_select('migrate_map_donkeymedia_file', 'm')
          ->fields('m', array('sourceid1', 'destid1'))
          ->condition('m.sourceid1', $image['attachment'])
          ->execute();
        $map = $q->fetchObject();
        if ($map) {
          $dest = array(
            'target_id' => $map->destid1,
            'alt' => 'Sorry. No describing text available.',
            'title' => 'No title',
          );
          // Alt Tag.
          if (!empty($image['alt'])) {
            $dest['alt'] = $image['alt'];
          }
          // Title.
          if (!empty($image['caption'])) {
            $dest['title'] = $image['caption'];
          }
          $return[] = $dest;
        }
        else {
          echo 'Source Id: "' . $image['attachment'] . '" not Found in File Map table (migrate_map_donkeymedia_file)' . "\n";
          throw new MigrateSkipProcessException();
        }
      }
    }
    if (!empty($return)) {
      $row->setDestinationProperty($destination_property, $return);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
