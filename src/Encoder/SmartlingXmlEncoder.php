<?php

/**
 * @file
 * Contains \Drupal\smartling\Encoder\SmartlingXmlEncoder.
 */

namespace Drupal\smartling\Encoder;

use Drupal\serialization\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder as SymfonyXmlEncoder;

/**
 * Encodes HAL data in JSON.
 *
 * Simply respond to hal_json format requests using the JSON encoder.
 */
class SmartlingXmlEncoder extends XmlEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var array
   */
  static protected $format = array('smartling_xml');

  /**
   * Gets the base encoder instance.
   *
   * @return \Symfony\Component\Serializer\Encoder\XmlEncoder
   *   The base encoder.
   */
  public function getBaseEncoder() {
    if (!isset($this->baseEncoder)) {
      $this->baseEncoder = new SymfonyXmlEncoder('document');
    }

    return $this->baseEncoder;
  }

}
