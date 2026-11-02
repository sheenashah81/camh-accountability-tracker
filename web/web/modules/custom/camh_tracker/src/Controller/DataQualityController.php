<?php

namespace Drupal\camh_tracker\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Editor report: what each commitment record still needs before or after publication.
 */
class DataQualityController extends ControllerBase {

  public function build(): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'commitment')->sort('title')->execute();
    $rows = [];
    $totals = ['records' => 0, 'complete' => 0, 'issues' => 0];
    foreach ($storage->loadMultiple($ids) as $node) {
      $issues = [];
      if ($node->get('field_status')->isEmpty()) {
        $issues[] = $this->t('No progress status');
      }
      if ($node->get('field_date_made')->isEmpty()) {
        $issues[] = $this->t('No date made');
      }
      $licence = $node->get('field_licence')->entity;
      if (!$licence || $licence->label() === 'Not yet checked') {
        $issues[] = $this->t('Licence not checked');
      }
      if ($node->get('field_source_url')->isEmpty()) {
        $issues[] = $this->t('No source link');
      }
      if ($node->get('field_verified_on')->isEmpty()) {
        $issues[] = $this->t('No verified-on date');
      }
      if ($node->hasTranslation('es') && ($node->getTranslation('es')->get('field_es_review_status')->value ?? 'pending') === 'pending') {
        $issues[] = $this->t('Spanish text pending human review');
      }
      $totals['records']++;
      $issues ? $totals['issues']++ : $totals['complete']++;
      $country = $node->get('field_country')->entity;
      $rows[] = [
        ['data' => $node->toLink($node->label())->toRenderable()],
        $country ? $country->label() : '',
        $node->get('moderation_state')->value ?? '',
        $node->get('field_is_illustrative')->value ? $this->t('Yes') : $this->t('No'),
        $issues ? ['data' => ['#theme' => 'item_list', '#items' => $issues]] : $this->t('None'),
      ];
    }
    $build['summary'] = [
      '#markup' => '<p>' . $this->t('@r commitment records: @c with nothing outstanding, @i with at least one item to resolve. Drafts are expected to have open items; a record should have none before it is published.', ['@r' => $totals['records'], '@c' => $totals['complete'], '@i' => $totals['issues']]) . '</p>',
    ];
    $build['table'] = [
      '#type' => 'table',
      '#caption' => $this->t('Commitment records and outstanding items'),
      '#header' => [$this->t('Commitment'), $this->t('Country'), $this->t('State'), $this->t('Illustrative'), $this->t('Outstanding')],
      '#rows' => $rows,
      '#empty' => $this->t('There are no commitment records.'),
    ];
    $build['values_summary'] = ['#markup' => '<h2>' . $this->t('Indicator values') . '</h2>'];
    $build['values'] = $this->indicatorValueTable();
    $build['#cache'] = ['max-age' => 0];
    return $build;
  }

  /**
   * Coverage and completeness of indicator values, one row per indicator and country.
   */
  protected function indicatorValueTable(): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'indicator_value')->execute();
    $groups = [];
    foreach ($storage->loadMultiple($ids) as $node) {
      $indicator = $node->get('field_indicator')->entity;
      $country = $node->get('field_country')->entity;
      $key = ($indicator ? $indicator->label() : '?') . '|' . ($country ? $country->label() : '?');
      $g = &$groups[$key];
      $g['indicator'] = $indicator ? $indicator->label() : '';
      $g['country'] = $country ? $country->label() : '';
      $g['years'][] = (int) $node->get('field_year')->value;
      $licence = $node->get('field_licence')->entity;
      $g['no_licence'] = ($g['no_licence'] ?? 0) + (!$licence || $licence->label() === 'Not yet checked' ? 1 : 0);
      $g['no_source'] = ($g['no_source'] ?? 0) + ($node->get('field_source_url')->isEmpty() ? 1 : 0);
      $g['no_retrieved'] = ($g['no_retrieved'] ?? 0) + ($node->get('field_retrieved_on')->isEmpty() ? 1 : 0);
      $g['illustrative'] = ($g['illustrative'] ?? 0) + ($node->get('field_is_illustrative')->value ? 1 : 0);
      unset($g);
    }
    ksort($groups);
    $rows = [];
    foreach ($groups as $g) {
      $years = array_unique($g['years']);
      sort($years);
      $first = reset($years);
      $last = end($years);
      $missing = ($last - $first + 1) - count($years);
      $issues = [];
      if ($missing > 0) {
        $issues[] = $this->t('@n years missing between @a and @b', ['@n' => $missing, '@a' => $first, '@b' => $last]);
      }
      if ($g['no_licence']) {
        $issues[] = $this->t('@n values with licence not checked', ['@n' => $g['no_licence']]);
      }
      if ($g['no_source']) {
        $issues[] = $this->t('@n values with no source link', ['@n' => $g['no_source']]);
      }
      if ($g['no_retrieved']) {
        $issues[] = $this->t('@n values with no retrieved-on date', ['@n' => $g['no_retrieved']]);
      }
      if ($last < (int) date('Y') - 4) {
        $issues[] = $this->t('Latest year (@y) is more than four years old; check the source for newer data', ['@y' => $last]);
      }
      $rows[] = [
        $g['indicator'], $g['country'], count($years), $first . '–' . $last,
        $g['illustrative'] ?: $this->t('None'),
        $issues ? ['data' => ['#theme' => 'item_list', '#items' => $issues]] : $this->t('None'),
      ];
    }
    return [
      '#type' => 'table',
      '#caption' => $this->t('Indicator values by indicator and country'),
      '#header' => [$this->t('Indicator'), $this->t('Country'), $this->t('Years held'), $this->t('Range'), $this->t('Illustrative values'), $this->t('Outstanding')],
      '#rows' => $rows,
      '#empty' => $this->t('There are no indicator values.'),
    ];
  }

}
