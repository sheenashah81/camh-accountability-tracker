<?php

namespace Drupal\camh_tracker\Plugin\views\filter;

use Drupal\views\Attribute\ViewsFilter;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Filters commitments by the year they were made or adopted.
 *
 * Shows a drop-down of the years that exist in the data.
 */
#[ViewsFilter('camh_year')]
class CamhYear extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $years = \Drupal::database()->query(
        "SELECT DISTINCT SUBSTRING(d.field_date_made_value, 1, 4) AS y FROM {node__field_date_made} d
         INNER JOIN {node_field_data} n ON n.nid = d.entity_id AND n.status = 1 WHERE d.bundle = 'commitment' ORDER BY y DESC"
      )->fetchCol();
      $this->valueOptions = array_combine($years, $years);
    }
    return $this->valueOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if (empty($this->value)) {
      return;
    }
    $this->ensureMyTable();
    $years = array_values(array_filter((array) $this->value));
    if (!$years) {
      return;
    }
    $field = "$this->tableAlias.$this->realField";
    $this->query->addWhereExpression($this->options['group'], "SUBSTRING($field, 1, 4) IN (:years[])", [':years[]' => $years]);
  }

}
