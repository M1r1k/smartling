<?php

/**
 * @file
 * Class SmartlingContentMediaEncodedParser.
 */

namespace Smartling\Alters;

use Smartling\Alters\SmartlingContentBaseParser;

/*
 * A parser for the Drupal media module and its jsons inside text fields.
 * For example: [[{&quot;fid&quot;:&quot;466&quot;,&quot;view_mode&quot;:&quot;default&quot;,&quot;fields&quot;:{&quot;format&quot;:&quot;default&quot;,&quot;field_file_image_alt_text[und][0][value]&quot;:&quot;&quot;,&quot;field_file_image_title_text[und][0][value]&quot;:&quot;&quot;},&quot;type&quot;:&quot;media&quot;,&quot;attributes&quot;:{&quot;class&quot;:&quot;media-element file-default&quot;}}]]
 */
class SmartlingContentMediaEncodedParser extends SmartlingContentBaseParser {
  protected $regexp = '~(\[\[\{.*&quot;fid&quot;:.+?\}\]\])~i';

  /*
   * Adds some context to the string that is being processed
   */
  protected function getContext($matches) {
    foreach($matches as $k => $v) {
      $matches[$k] = json_decode(htmlspecialchars_decode($v));
    }

    return $matches;
  }

}