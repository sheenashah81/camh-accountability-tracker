<?php

namespace Drupal\camh_tracker\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * The Data page and the CSV and JSON downloads (requirement FR-09).
 *
 * Only published records are exported, in the language of the page address
 * (/en or /es). Every row carries its source, licence and the date it was
 * checked or retrieved.
 */
class DataController extends ControllerBase {

  protected const COMMITMENT_COLUMNS = [
    'country_iso3', 'country', 'institution', 'title', 'themes', 'commitment_type', 'description', 'date_made', 'target_date',
    'status', 'status_updated_on', 'source_name', 'source_url', 'licence', 'verified_on', 'is_illustrative', 'language', 'record_url',
  ];

  protected const VALUE_COLUMNS = [
    'indicator_code', 'indicator', 'country_iso3', 'country', 'year', 'value', 'unit', 'sex', 'notes',
    'source_name', 'source_url', 'licence', 'retrieved_on', 'is_illustrative', 'language',
  ];

  /**
   * Label of an entity in the current content language.
   */
  protected function label($entity): string {
    return $entity ? \Drupal::service('entity.repository')->getTranslationFromContext($entity)->label() : '';
  }

  /**
   * Rows for the published commitments.
   */
  public function commitmentRows(): array {
    $nodes = $this->entityTypeManager()->getStorage('node');
    $repo = \Drupal::service('entity.repository');
    $lang = $this->languageManager()->getCurrentLanguage()->getId();
    $ids = $nodes->getQuery()->accessCheck(TRUE)->condition('type', 'commitment')->condition('status', 1)->sort('field_country.entity:node.title')->sort('title')->execute();
    $rows = [];
    foreach ($nodes->loadMultiple($ids) as $n) {
      $n = $repo->getTranslationFromContext($n);
      $country = $n->get('field_country')->entity;
      $themes = array_map(fn($t) => $this->label($t), $n->get('field_theme')->referencedEntities());
      $rows[] = [
        'country_iso3' => $country ? $country->get('field_iso3')->value : '',
        'country' => $this->label($country),
        'institution' => $this->label($n->get('field_institution')->entity),
        'title' => $n->label(),
        'themes' => implode('; ', $themes),
        'commitment_type' => $this->label($n->get('field_commitment_type')->entity),
        'description' => trim(strip_tags((string) $n->get('field_description')->value)),
        'date_made' => $n->get('field_date_made')->value,
        'target_date' => $n->get('field_target_date')->value,
        'status' => $this->label($n->get('field_status')->entity),
        'status_updated_on' => $n->get('field_status_updated_on')->value,
        'source_name' => $n->get('field_source_name')->value,
        'source_url' => $n->get('field_source_url')->uri,
        'licence' => $this->label($n->get('field_licence')->entity),
        'verified_on' => $n->get('field_verified_on')->value,
        'is_illustrative' => $n->get('field_is_illustrative')->value ? 'true' : 'false',
        'language' => $lang,
        'record_url' => $n->toUrl('canonical', ['absolute' => TRUE])->toString(),
      ];
    }
    return $rows;
  }

  /**
   * Rows for the published indicator values.
   */
  public function valueRows(): array {
    $nodes = $this->entityTypeManager()->getStorage('node');
    $lang = $this->languageManager()->getCurrentLanguage()->getId();
    $ids = $nodes->getQuery()->accessCheck(TRUE)->condition('type', 'indicator_value')->condition('status', 1)
      ->sort('field_indicator.entity:node.title')->sort('field_country.entity:node.title')->sort('field_year')->execute();
    $rows = [];
    $repo = \Drupal::service('entity.repository');
    foreach ($nodes->loadMultiple($ids) as $n) {
      $indicator = $n->get('field_indicator')->entity;
      $country = $n->get('field_country')->entity;
      $indicator_t = $indicator ? $repo->getTranslationFromContext($indicator) : NULL;
      $rows[] = [
        'indicator_code' => $indicator ? $indicator->get('field_code')->value : '',
        'indicator' => $indicator_t ? $indicator_t->label() : '',
        'country_iso3' => $country ? $country->get('field_iso3')->value : '',
        'country' => $this->label($country),
        'year' => $n->get('field_year')->value,
        'value' => rtrim(rtrim((string) $n->get('field_value')->value, '0'), '.'),
        'unit' => $indicator_t ? $indicator_t->get('field_unit')->value : '',
        'sex' => $n->get('field_sex')->value,
        'notes' => $n->get('field_notes')->value,
        'source_name' => $n->get('field_source_name')->value,
        'source_url' => $n->get('field_source_url')->uri,
        'licence' => $this->label($n->get('field_licence')->entity),
        'retrieved_on' => $n->get('field_retrieved_on')->value,
        'is_illustrative' => $n->get('field_is_illustrative')->value ? 'true' : 'false',
        'language' => $lang,
      ];
    }
    return $rows;
  }

  /**
   * Protects spreadsheet users: a cell must never start as a formula.
   */
  protected function safe($value): string {
    $value = (string) $value;
    return $value !== '' && strpbrk($value[0], "=+-@\t\r") !== FALSE && !is_numeric($value) ? "'" . $value : $value;
  }

  protected function respond(string $name, string $format, array $columns, array $rows): CacheableResponse {
    $meta = (new CacheableMetadata())->addCacheTags(['node_list'])->addCacheContexts(['languages:language_content', 'url.path']);
    if ($format === 'json') {
      $body = json_encode([
        'meta' => [
          'dataset' => $name, 'generated' => date('Y-m-d'), 'language' => $this->languageManager()->getCurrentLanguage()->getId(),
          'records' => count($rows), 'only_published_records' => TRUE,
          'licence_note' => 'Reuse of each row follows the licence stated in its licence column and the terms of its source document. Source names and URLs are given in every row.',
          'site' => Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(),
        ],
        'data' => $rows,
      ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      $response = new CacheableResponse($body, 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }
    else {
      $fh = fopen('php://temp', 'r+');
      fputcsv($fh, $columns, ',', '"', '\\');
      foreach ($rows as $row) {
        fputcsv($fh, array_map(fn($c) => $this->safe($row[$c] ?? ''), $columns), ',', '"', '\\');
      }
      rewind($fh);
      // A byte order mark lets spreadsheet programs read accents correctly.
      $response = new CacheableResponse("\xEF\xBB\xBF" . stream_get_contents($fh), 200, ['Content-Type' => 'text/csv; charset=utf-8']);
      fclose($fh);
    }
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $name . '-' . $this->languageManager()->getCurrentLanguage()->getId() . '.' . $format . '"');
    $response->addCacheableDependency($meta);
    return $response;
  }

  public function commitments(string $format): CacheableResponse {
    return $this->respond('commitments', $format, self::COMMITMENT_COLUMNS, $this->commitmentRows());
  }

  public function indicatorValues(string $format): CacheableResponse {
    return $this->respond('indicator-values', $format, self::VALUE_COLUMNS, $this->valueRows());
  }

  /**
   * The Data page.
   */
  public function page(): array {
    $c = count($this->commitmentRows());
    $v = count($this->valueRows());
    $link = fn(string $route, string $format) => ['#type' => 'link', '#title' => strtoupper($format), '#url' => Url::fromRoute($route . '_' . $format), '#attributes' => ['class' => ['download-link'], 'download' => '']];
    $rows = [
      [
        ['data' => ['#markup' => '<strong>' . t('Commitments') . '</strong><br><span class="source-note">' . t('Published commitments with their institution, status, dates, source, licence and the date the source was checked.') . '</span>'], 'data-label' => t('Dataset')],
        ['data' => (string) $c, 'data-label' => t('Records')],
        ['data' => ['#type' => 'container', 'csv' => $link('camh_tracker.data_commitments', 'csv'), 'sep' => ['#markup' => ' '], 'json' => $link('camh_tracker.data_commitments', 'json')], 'data-label' => t('Download')],
      ],
      [
        ['data' => ['#markup' => '<strong>' . t('Indicator values') . '</strong><br><span class="source-note">' . t('Yearly values of each indicator by country, with unit, source, licence and the date retrieved.') . '</span>'], 'data-label' => t('Dataset')],
        ['data' => (string) $v, 'data-label' => t('Records')],
        ['data' => ['#type' => 'container', 'csv' => $link('camh_tracker.data_values', 'csv'), 'sep' => ['#markup' => ' '], 'json' => $link('camh_tracker.data_values', 'json')], 'data-label' => t('Download')],
      ],
    ];
    return [
      '#attached' => ['library' => ['camh_tracker/interim']],
      '#cache' => ['tags' => ['node_list'], 'contexts' => ['languages:language_content']],
      'intro' => ['#markup' => '<p class="overview-intro">' . t('Download the published data as CSV (opens in a spreadsheet) or JSON (for programs). Only published records are included, in the language of this page.') . '</p>'],
      'table' => ['#type' => 'table', '#caption' => t('Datasets'), '#header' => [t('Dataset'), t('Records'), t('Download')], '#rows' => $rows, '#attributes' => ['class' => ['data-downloads']]],
      'licence' => ['#markup' => '<h2>' . t('Licences and attribution') . '</h2><p>' . t('Every row names its source and licence. Indicator values come from the World Bank under CC BY 4.0: credit the World Bank and the original source (WHO, UN Population Division). Commitment rows summarise public documents in our own words; reuse of the underlying documents follows each source\'s licence, and some sources allow non-commercial use only. Check the licence column before reusing a row.') . '</p>'],
      'api' => ['#markup' => '<h2>' . t('Programming interface') . '</h2><p>' . t('A read-only JSON:API is available at /jsonapi for published records. It cannot be used to change data.') . '</p>'],
      'caveat' => ['#markup' => '<p class="source-note">' . t('Records still in draft are not included. Spanish text is AI-assisted and has been reviewed by a person.') . '</p>'],
    ];
  }

}
