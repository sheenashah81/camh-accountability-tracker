<?php

namespace Drupal\camh_tracker\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Checks and loads commitment rows from a CSV file.
 *
 * The file uses the column layout of data/commitments.csv. Every valid row is
 * loaded as a Draft: the workflow_state column is not used to publish, because
 * publishing goes through editorial review. Rows that match an existing
 * commitment (same title and country) are skipped. Rows that break a rule in
 * the camh_tracker validation are reported and not loaded.
 */
class CommitmentCsvImporter {

  const REQUIRED_COLUMNS = [
    'country_iso3', 'title_en', 'slug', 'institution_code', 'theme', 'commitment_type',
    'description_en', 'source_name', 'source_url', 'licence', 'verified_on', 'is_illustrative',
  ];

  protected array $terms = [];

  public function __construct(protected EntityTypeManagerInterface $etm) {}

  /**
   * Reads, checks and optionally saves the rows.
   *
   * @return array
   *   ['fatal' => string|null, 'rows' => list of [row, title, result, message]].
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
      return ['fatal' => 'Missing columns: ' . implode(', ', $missing) . '. Use the column headings of data/commitments.csv.', 'rows' => []];
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
      $key = $row['country_iso3'] . '|' . $row['title_en'];
      if (isset($seen[$key])) {
        $results[] = [$line, $row['title_en'], 'error', "Repeats row {$seen[$key]} in this file."];
        continue;
      }
      $seen[$key] = $line;
      [$result, $message] = $this->handle($row, $save);
      $results[] = [$line, $row['title_en'], $result, $message];
    }
    fclose($fh);
    return ['fatal' => NULL, 'rows' => $results];
  }

  protected function handle(array $row, bool $save): array {
    $problems = [];
    foreach (['country_iso3', 'title_en', 'slug', 'description_en', 'source_name'] as $col) {
      if ($row[$col] === '') {
        $problems[] = "$col is empty";
      }
    }
    $country = $this->nodeId('country', 'field_iso3', $row['country_iso3']);
    if ($row['country_iso3'] !== '' && !$country) {
      $problems[] = "country {$row['country_iso3']} is not in the tracker";
    }
    $institution = $this->nodeId('institution', 'field_code', $row['institution_code']);
    if (!$institution) {
      $problems[] = "institution code {$row['institution_code']} is not in the tracker";
    }
    $type = $this->term('commitment_type', $row['commitment_type']);
    if (!$type) {
      $problems[] = "commitment type \"{$row['commitment_type']}\" is not a listed value";
    }
    $licence = $this->term('licence', $row['licence']);
    if (!$licence) {
      $problems[] = "licence \"{$row['licence']}\" is not a listed value";
    }
    $themes = [];
    foreach (array_filter(explode('|', $row['theme'])) as $t) {
      if ($id = $this->term('theme', trim($t))) {
        $themes[] = ['target_id' => $id];
      }
      else {
        $problems[] = "theme \"$t\" is not a listed value";
      }
    }
    $status = NULL;
    if (($row['status'] ?? '') !== '') {
      $status = $this->term('status', $row['status']);
      if (!$status) {
        $problems[] = "status \"{$row['status']}\" is not a listed value";
      }
    }
    foreach (['date_made', 'target_date', 'status_updated_on', 'verified_on'] as $col) {
      if (($row[$col] ?? '') !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $row[$col])) {
        $problems[] = "$col must be written YYYY-MM-DD";
      }
    }
    if ($problems) {
      return ['error', implode('; ', $problems) . '.'];
    }
    if ($this->etm->getStorage('node')->loadByProperties(['type' => 'commitment', 'title' => $row['title_en'], 'field_country' => $country])) {
      return ['skipped', 'A commitment with this title already exists for this country.'];
    }
    $blank = fn($v) => ($v === NULL || $v === '') ? NULL : $v;
    $node = Node::create([
      'type' => 'commitment', 'title' => $row['title_en'], 'langcode' => 'en', 'status' => 0,
      'field_country' => $country, 'field_institution' => $institution, 'field_theme' => $themes,
      'field_commitment_type' => $type,
      'field_description' => ['value' => $row['description_en'], 'format' => 'basic_html'],
      'field_date_made' => $blank($row['date_made'] ?? NULL), 'field_target_date' => $blank($row['target_date'] ?? NULL),
      'field_status' => $status, 'field_status_updated_on' => $blank($row['status_updated_on'] ?? NULL),
      'field_source_name' => $row['source_name'], 'field_licence' => $licence,
      'field_verified_on' => $blank($row['verified_on']),
      'field_is_illustrative' => $row['is_illustrative'] === 'true' ? 1 : 0,
      'field_verification_status' => $blank($row['verification_status'] ?? NULL),
      'field_verification_note' => ['value' => $row['verification_note'] ?? '', 'format' => 'plain_text'],
      'field_es_review_status' => 'pending',
      'path' => ['alias' => '/commitments/' . $row['slug'], 'pathauto' => 0],
      'moderation_state' => 'draft',
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
      return ['ok', 'Valid. Would be loaded as a Draft.'];
    }
    $node->save();
    return ['loaded', 'Loaded as a Draft.'];
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
