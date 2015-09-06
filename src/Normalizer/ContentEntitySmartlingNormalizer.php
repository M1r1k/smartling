<?php

/**
 * @file
 * Contains \Drupal\serialization\Normalizer\ContentEntityNormalizer.
 */

namespace Drupal\smartling\Normalizer;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\serialization\Normalizer\EntityNormalizer;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 */
class ContentEntitySmartlingNormalizer extends EntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var array
   */
  protected $supportedInterfaceOrClass = ['Drupal\Core\Entity\ContentEntityInterface'];

  protected $format = 'smartling_xml';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = array()) {
    $context += array(
      'account' => NULL,
    );

    $attributes = [];
    $global_exclude = [
      $entity->getEntityType()->getKey('revision'),
      $entity->getEntityType()->getKey('uuid'),
      $entity->getEntityType()->getKey('langcode'),
    ];
    foreach ($entity as $name => $field) {
      /** @var FieldItemListInterface $field */
      if (!in_array($name, $global_exclude) && $field->access('view', $context['account']) && $field->getFieldDefinition()->isTranslatable()) {
        $attributes[] = ['@name' => $name, 'data' => $this->serializer->normalize($field, 'xml', $context)];
      }
    }

    return $attributes;
  }

}
