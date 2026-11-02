<?php

namespace Drupal\camh_tracker\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface as RefList;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Shows a status term as a badge: colour border, icon and text label.
 */
#[FieldFormatter(
  id: 'camh_status_badge',
  label: new TranslatableMarkup('Status badge'),
  field_types: ['entity_reference'],
)]
class StatusBadgeFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];
    if (!$items instanceof RefList) {
      return $elements;
    }
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $term) {
      $elements[$delta] = camh_tracker_status_badge($term);
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable($field_definition): bool {
    return $field_definition->getFieldStorageDefinition()->getSetting('target_type') === 'taxonomy_term';
  }

}
