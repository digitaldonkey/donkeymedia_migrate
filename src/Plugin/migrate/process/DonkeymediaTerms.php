<?php
namespace Drupal\donkeymedia_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Vocabulary;



/**
 * Description of MixingbowlRecipeCategories
 *
 * @MigrateProcessPlugin(
 *   id = "donkeymedia_terms"
 * )
 *
 * @author thorsten
 */
class DonkeymediaTerms extends ProcessPluginBase {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $X = FALSE;


    $vocabularies = taxonomy_vocabulary_get_names();

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
