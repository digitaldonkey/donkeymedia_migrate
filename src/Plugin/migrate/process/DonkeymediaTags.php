<?php
namespace Drupal\donkeymedia_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\CachedStorage;


/**
 * Description of MixingbowlRecipeCategories
 *
 * @MigrateProcessPlugin(
 *   id = "donkeymedia_tags"
 * )
 *
 * @author mike
 */
class DonkeymediaTags extends ProcessPluginBase {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $entity_type = $row->getDestination()['type'];
    $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $entity_type);


    if (isset($definitions[$destination_property])) {

      $vid = $this->configuration['vid'];
      $tid_qry = db_select('taxonomy_term_field_data', 't')
        ->fields('t', array('tid'))
        ->condition('name', $value)
        ->execute();
      $db_row = $tid_qry->fetchAssoc();
      if ($db_row !== FALSE) {
        return $db_row['tid'];
      }
      else {
        trigger_error("Missing term '$value' under vid '$vid'", E_USER_WARNING);
        return array();
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
