<?php
namespace Drupal\mixingbowl_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Description of MixingbowlRecipeCategories
 *
 * @MigrateProcessPlugin(
 *   id = "mixingbowl_recipe_categories"
 * )
 * 
 * @author mike
 */
class MixingbowlRecipeCategories extends ProcessPluginBase {
  
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $vid = $this->configuration['vid'];
    $tid_qry = db_select('taxonomy_term_field_data', 't')
            ->fields('t', array('tid'))
            ->condition('vid', $vid)
            ->condition('name', $value)
            ->execute();
    $db_row = $tid_qry->fetchAssoc();
    if($db_row !== false) {
      return $db_row['tid'];
    } else {
      //trigger_error("Missing term '$value' under vid '$vid'", E_USER_WARNING);
      return '';
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }
  
}
