<?php
/**
 * Example event subscriber.
 */

// Declare the namespace that our event subscriber is in. This should follow the
// PSR-4 standard, and use the EventSubscriber sub-namespace.
namespace Drupal\donkeymedia_migrate\EventSubscriber;

// This is the interface we are going to implement.
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Drupal\migrate\Event\MigrateEvents;
// This class contains the event we want to subscribe to.
use \Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\donkeymedia_migrate\Wordpress\WordpressTagsParser;



/**
 * Subscribe to MigrateEvents::POST_ROW_SAVE events.
 */
class DonkeymediaMigratePostSave implements EventSubscriberInterface {

  use WordpressTagsParser;

  /**
   * {@inheritdoc}
   *
   * Publish the Event.
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE][] = array('updateTranslations');
    return $events;
  }

  /**
   * MigrateEvents::POST_ROW_SAVE event handler.
   *
   * @param GetResponseEvent $event
   *   Instance of Symfony\Component\HttpKernel\Event\GetResponseEvent.
   */
  public function updateTranslations(GetResponseEvent $event) {

    $row = $event->getRow();
    $migrate_src_values = $row->getSource();
    $migrate_dest_values = $row->getDestination();

    // Make sure that this post processing is enables for this migration.
    if (!isset($migrate_src_values['constants']['post_save_process']) || $migrate_src_values['constants']['post_save_process'] != 'DonkeymediaMigratePostSave') {
      return $event;
    }

    module_load_include('inc', 'donkeymedia_migrate', 'qtranslate_parser');

    // These are defined in migration.yml.
    $available_languages = $row->getSource()['constants']['available_languages'];
    $default_language = $row->getDestination()['langcode'];

    // Unset default language from available langguages.
    if (($key = array_search($default_language, $available_languages)) !== FALSE) {
      unset($available_languages[$key]);
    }

    $migrated_node = $event->destinationIdValues[0];
    $entity = node_load($migrated_node);

    // Get multilingual fields in all languages.
    $titles = qtrans_split($migrate_src_values['title'], $available_languages);
    $body_values = qtrans_split($migrate_src_values['content:encoded'], $available_languages);

    foreach ($available_languages as $lang) {

      // Preprocess Body:
      // Remove Caption and prepare Image.
      $caption_tags = $this->getTags($body_values[$lang]);

      // Remove Caption.
      $body_value = $this->getTags($body_values[$lang], TRUE);

      // Only add translation if we have some translated Stuff.
      $check_values = isset($titles[$lang]) && strlen($titles[$lang]) > 0 && isset($body_values[$lang]) && strlen($body_values[$lang]) > 0;
      $check_content = TRUE; //($titles[$lang] != $entity->getTitle()) || $body_values[$lang] != $entity->get('body');

      $has_images = (count($caption_tags['caption']['values']) > 0);
      if ($has_images) {
        $X = FALSE;
      }

      if ($check_values && $check_content) {
        $values = array(
          // Non multilingual.
          'created' => $migrate_dest_values['created'],
          'uid' => $migrate_dest_values['uid'],
          'sticky' => $migrate_dest_values['sticky'],
          'status' => $migrate_dest_values['status'],

          // Multilingual (Images need extra treatment. See below.).
          'title' => $titles[$lang],
          'body' => array(
            'value' => $body_value,
            'format' => $migrate_dest_values['body']['format'],
          ),
        );

        if ($has_images) {
          $images = array();

          foreach ($caption_tags['caption']['values'] as $img) {

            $q = db_select('migrate_map_donkeymedia_file', 'm')
              ->fields('m', array('sourceid1', 'destid1'))
              ->condition('m.sourceid1', $img['attachment'])
              ->execute();
            $map = $q->fetchObject();

            $file = \Drupal::entityManager()->getStorage('file')->load($map->destid1);

            if (is_object($file)) {
              $image_values = array(
                // File is set not translatable in Article and Project.
                'target_id' => $file->id(),
                'alt' => $img['alt'],
                'title' => $img['caption'],
              );
              $images[] = $image_values;
              $file = FALSE;
            }
            else {
              trigger_error("Can't find file:", E_USER_WARNING);
              var_dump($img);
            }
          }

          // Directly setting the values doesn't work :/
          // $values['field_image'] = $images;
        }

        $translated_entity = $entity->addTranslation($lang, $values);
        if ($has_images && !empty($images)) {
          $translated_entity->save();
          $translated_entity->field_image->setValue($images);
          $translated_entity->setChangedTime($migrate_dest_values['changed']);
          $translated_entity->save();
        }
        else {
          $translated_entity->setChangedTime($migrate_dest_values['changed']);
          $translated_entity->save();
        }
      }
    }

    $map = $event->getMigration()->getIdMap();
    $map->saveIdMapping($event->getRow(), array($migrated_node));
  }

}
