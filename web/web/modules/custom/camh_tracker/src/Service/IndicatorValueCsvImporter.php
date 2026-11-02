<?php

namespace Drupal\camh_tracker\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Checks and loads indicator-value rows from a CSV file.
 *
 * The file uses the column layout of data/indicator_values.csv. Valid rows are
 * created unpublished so that an editor can review them and publish them in
 * one step from the content overview. Rows that match an existing value
 * (indicator, country, year and sex) are skipped. Rows that break a rule in
 * the camh_tracker validation are reported and not loaded.
 */
class IndicatorValueCsvImporter {

  const REQUIRED_COLUMNS = [
    'indicator_code', 'country_iso3', 'year', 'value', 'source_name', 'source_url',
    'licence', 'retrieved_on', 'is_illustrative',
  ];

  protected array $terms = [];

  public function __construct(protected EntityTypeManagerInterface $etm) {}

  /**
   * Reads, checks and optionally saves the rows.
   *
   * @return array
   *   ['fatal' => string|null, 'rows' => list of [row, label, result, message]].
   */
  public function process(string $path, bool $save): array {
    $fh = fopen($path, 'r');
    $head = $fh ? fgetcsv($fh, 0, ',', '"', '\\') : FALSE;
    if (!$head) {
      return ['fatal' => 'The file is empty or is not a readable CSV.', 'rows' => []];
    }
    $head[0] = preg_replace('/^\xEF\xBB\xBF/', '', $head[0]);
    $missing = array_diff(self::REQUIRED_COLUMNS, $head);
    if ($missing) {
      return ['fatal' => 'Missing columns: ' . implode(', ', $missing) . '. Use the column headings of data/indicator_values.csv.', 'rows' => []];
    }
    $results = [];
    $line = 1;
    $seen = [];
    while (($r = fgetcsv($fh, 0, ',', '"', '\\')) !== FALSE) {
      $line++;
      if ($r === [NULL]) {
        continue;
      }
      if (count($r) !== count($head)) {
        $results[] = [$line, '', 'error', 'The row has ' . count($r) . ' values but the header has ' . count($head) . '. Check for a missing or extra comma.'];
        continue;
      }
      $row = array_combine($head, array_map('trim', $r));
      $label = trim("{$row['indicator_code']} {$row['country_iso3']} {$row['year']} " . ($row['sex'] ?? ''));
      $key = strtolower($label);
      if (isset($seen[$key])) {
        $results[] = [$line, $label, 'error', "Repeats row {$seen[$key]} in this file."];
        continue;
      }
      $seen[$key] = $line;
      [$result, $message] = $this->handle($row, $save);
      $results[] = [$line, $label, $result, $message];
    }
    fclose($fh);
    return ['fatal' => NULL, 'rows' => $results];
  }

  protected function handle(array $row, bool $save): array {
    $problems = [];
    $indicator = $this->nodeId('indicator', 'field_code', $row['indicator_code']);
    if (!$indicator) {
      $problems[] = "indicator code {$row['indicator_code']} is not in the tracker";
    }
    $country = $this->nodeId('country', 'field_iso3', $row['country_iso3']);
    if (!$country) {
      $problems[] = "country {$row['country_iso3']} is not in the tracker";
    }
    if (!preg_match('/^\d{4}$/', $row['year'])) {
      $problems[] = 'year must be four digits';
    }
    if (!is_numeric($row['value'])) {
      $problems[] = 'value must be a number';
    }
    if ($row['source_name'] === '') {
      $problems[] = 'source_name is empty';
    }
    $licence = $this->term('licence', $row['licence']);
    if (!$licence) {
      $problems[] = "licence \"{$row['licence']}\" is not a listed value";
    }
    $sex = ($row['sex'] ?? '') === '' ? NULL : $row['sex'];
    if ($sex !== NULL && !in_array($sex, ['Total', 'Female', 'Male'], TRUE)) {
      $problems[] = "sex \"$sex\" must be Total, Female or Male";
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['retrieved_on'])) {
      $problems[] = 'retrieved_on must be written YYYY-MM-DD';
    }
    if ($problems) {
      return ['error', implode('; ', $problems) . '.'];
    }
    $cond = ['type' => 'indicator_value', 'field_indicator' => $indicator, 'field_country' => $country, 'field_year' => (int) $row['year']];
    if ($sex !== NULL) {
      $cond['field_sex'] = $sex;
    }
    if ($this->etm->getStorage('node')->loadByProperties($cond)) {
      return ['skipped', 'A value for this indicator, country, year and sex already exists.'];
    }
    $node = Node::create([
      'type' => 'indicator_value', 'langcode' => 'en', 'status' => 0,
      'title' => "{$row['indicator_code']} | {$row['country_iso3']} | {$row['year']} | " . ($sex ?? ''),
      'field_indicator' => $indicator, 'field_country' => $country,
      'field_year' => (int) $row['year'], 'field_value' => $row['value'], 'field_sex' => $sex,
      'field_notes' => $row['notes'] ?? '', 'field_source_name' => $row['source_name'],
      'field_licence' => $licence, 'field_retrieved_on' => $row['retrieved_on'],
      'field_is_illustrative' => $row['is_illustrative'] === 'true' ? 1 : 0,
    ]);
    if ($row['source_url'] !== '') {
      $node->set('field_source_url', ['uri' => $row['source_url']]);
    }
    $violations = $node->validate();
    if (count($violations)) {
      $msgs = [];
      foreach ($violations as $v) {
        $msgs[] = strip_tags((string) $v->getMessage());
      }
      return ['error', implode('; ', array_unique($msgs))];
    }
    if (!$save) {
      return ['ok', 'Valid. Would be loaded unpublished for review.'];
    }
    $node->save();
    return ['loaded', 'Loaded unpublished, ready for review.'];
  }

  protected function term(string $vid, string $label): ?int {
    if (!isset($this->terms[$vid])) {
      $this->terms[$vid] = [];
      foreach ($this->etm->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid]) as $t) {
        $this->terms[$vid][$t->label()] = (int) $t->id();
      }
    }
    return $this->terms[$vid][$label] ?? NULL;
  }

  protected function nodeId(string $type, string $field, string $value): ?int {
    if ($value === '') {
      return NULL;
    }
    $ids = $this->etm->getStorage('node')->getQuery()->accessCheck(FALSE)
      ->condition('type', $type)->condition($field, $value)->range(0, 1)->execute();
    return $ids ? (int) reset($ids) : NULL;
  }

}
