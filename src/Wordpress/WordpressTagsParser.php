<?php
/**
 * @file
 * Contains \Drupal\donkeymedia_migrate\Wordpress.
 */

namespace Drupal\donkeymedia_migrate\Wordpress;


trait WordpressTagsParser {


  private $shortcode_tags = array(

    'caption' => array(
      'pattern' => '/\[caption(.*)\[\/caption\]/mi',
      'function' => 'process_caption',
      'values' => array(),
    ),
    // '[embed]' => '__return_false',
    // '[wp_caption]' => 'img_caption_shortcode',
    // '[gallery]' => 'gallery_shortcode',
    // '[playlist]' => 'wp_playlist_shortcode',
    // '[audio]' => 'wp_audio_shortcode',
    // '[video]' => 'wp_video_shortcode',
    // '[contact_form]' => 'mclf_tag_replace',
    // '[readme]' => 'readme_parser',
    // '[readme_banner]' => 'readme_banner',
    // '[readme_info]' => 'readme_info',
  );

  /**
   * Process content.
   *
   * Finds any Tags defined in $this->shortcode_tags and returns this array
   * populated with values.
   */
  public function getTags($content, $remove_tags = FALSE) {
    $return = $this->shortcode_tags;
    if (strpos($content, '[') === FALSE) {
      $return = $remove_tags ? $content : NULL;
    }
    if (is_string($content)) {
      foreach ($this->shortcode_tags as $tag_id => $tag) {

        // Match every shortcode tag.
        if (preg_match_all($tag['pattern'], $content, $tag['matches'], PREG_SET_ORDER) != FALSE) {
          foreach ($tag['matches'] as $match) {
            $return[$tag_id]['values'][] = $this->$tag['function']($match[1]);
          }
        }

        if ($remove_tags) {
          $return = preg_replace($tag['pattern'], '', $content);
        }
      }
    }
    return $return;
  }



  private function process_caption($content) {
    $values = array();

    if (preg_match('/<(\s+)?img(?:.*alt=["\'](.*?)["\'].*)\/>?/i', $content, $alt)) {
      $values['alt'] = $alt[2];
    }
    if (preg_match('/(caption=["\'](.*?)["\'].*)\/>?/i', $content, $caption)) {
      $values['caption'] = $caption[2];
    }
    if (preg_match('/(id=["\']attachment_(.*?)["\'].*)\/>?/i', $content, $fid)) {
      $values['attachment'] = $fid[2];
    }

    // TODO Store Values?!

    // Replace the tag with empty string.
    return $values;
  }

}
