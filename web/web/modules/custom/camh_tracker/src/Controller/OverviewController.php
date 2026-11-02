<?php

namespace Drupal\camh_tracker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * The Overview page: key figures, map, progress summary and recent updates.
 */
class OverviewController extends ControllerBase {

  /**
   * Builds the page. Everything shown is computed from published records.
   */
  public function build(): array {
    $nodes = $this->entityTypeManager()->getStorage('node');
    $repo = \Drupal::service('entity.repository');
    $date = \Drupal::service('date.formatter');
    $count = fn(array $conditions, bool $access = TRUE) => (function () use ($nodes, $conditions, $access) {
      $q = $nodes->getQuery()->accessCheck($access)->count();
      foreach ($conditions as $f => $v) {
        $q->condition($f, $v);
      }
      return (int) $q->execute();
    })();

    // Countries and their published commitments.
    $countries = [];
    foreach ($nodes->loadMultiple($nodes->getQuery()->accessCheck(TRUE)->condition('type', 'country')->condition('status', 1)->sort('title')->execute()) as $c) {
      $c = $repo->getTranslationFromContext($c);
      $published = $count(['type' => 'commitment', 'status' => 1, 'field_country' => $c->id()]);
      $region = $c->get('field_region')->entity;
      $region = $region ? $repo->getTranslationFromContext($region)->label() : '';
      $countries[] = ['iso3' => $c->get('field_iso3')->value, 'name' => $c->label(), 'url' => $c->toUrl()->toString(), 'region' => $region, 'published' => $published];
    }
    $published_total = $count(['type' => 'commitment', 'status' => 1]);
    $in_preparation = $count(['type' => 'commitment', 'status' => 0], FALSE);
    $values = $count(['type' => 'indicator_value', 'status' => 1]);
    $years = \Drupal::database()->query('SELECT MIN(field_year_value) a, MAX(field_year_value) b FROM {node__field_year} WHERE bundle = :b', [':b' => 'indicator_value'])->fetchObject();

    $tiles = [
      [t('Countries tracked'), count($countries), t('Starter countries chosen for this prototype')],
      [t('Published commitments'), $published_total, t('Each one links to its public source')],
      [t('Records in preparation'), $in_preparation, t('Drafts waiting for review, a licence check or a status assessment')],
      [t('Indicator data points'), camh_tracker_format_number($values), $years && $years->a ? t('Years @a to @b, from the World Bank', ['@a' => $years->a, '@b' => $years->b]) : ''],
    ];
    $tiles_markup = '';
    foreach ($tiles as [$label, $value, $note]) {
      $tiles_markup .= '<div class="kpi-tile"><p class="kpi-tile__label">' . $label . '</p><p class="kpi-tile__value">' . $value . '</p><p class="kpi-tile__note">' . $note . '</p></div>';
    }

    // Progress summary: published commitments by status.
    $rows = [];
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'status']);
    uasort($terms, fn($a, $b) => $a->getWeight() <=> $b->getWeight());
    $max = 1;
    $counts = [];
    foreach ($terms as $t) {
      $counts[$t->id()] = $count(['type' => 'commitment', 'status' => 1, 'field_status' => $t->id()]);
      $max = max($max, $counts[$t->id()]);
    }
    foreach ($terms as $t) {
      $n = $counts[$t->id()];
      $rows[] = [
        ['data' => camh_tracker_status_badge($t), 'data-label' => t('Status')],
        ['data' => ['#type' => 'inline_template', '#template' => '<span class="status-bar" aria-hidden="true"><span style="width: {{ pct }}%"></span></span> <strong>{{ n }}</strong>', '#context' => ['pct' => round(100 * $n / $max), 'n' => $n]], 'data-label' => t('Published commitments')],
      ];
    }

    // Recent progress updates.
    $update_rows = [];
    $ids = $nodes->getQuery()->accessCheck(TRUE)->condition('type', 'progress_update')->condition('status', 1)->sort('field_update_date', 'DESC')->range(0, 5)->execute();
    foreach ($nodes->loadMultiple($ids) as $u) {
      $u = $repo->getTranslationFromContext($u);
      $commitment = $u->get('field_commitment')->entity;
      if (!$commitment) {
        continue;
      }
      $commitment = $repo->getTranslationFromContext($commitment);
      $country = $commitment->get('field_country')->entity;
      $summary = trim(strip_tags((string) $u->get('field_summary')->value));
      $update_rows[] = [
        ['data' => $date->format(strtotime($u->get('field_update_date')->value), 'camh_date'), 'data-label' => t('Date')],
        ['data' => $country ? $repo->getTranslationFromContext($country)->label() : '', 'data-label' => t('Country')],
        ['data' => ['#type' => 'link', '#title' => $commitment->label(), '#url' => $commitment->toUrl()], 'data-label' => t('Commitment')],
        ['data' => $u->get('field_new_status')->entity ? camh_tracker_status_badge($u->get('field_new_status')->entity) : '', 'data-label' => t('Status')],
        ['data' => mb_strlen($summary) > 160 ? mb_substr($summary, 0, 157) . '...' : $summary, 'data-label' => t('Summary')],
      ];
    }

    $country_rows = array_map(fn($c) => [
      ['data' => ['#type' => 'link', '#title' => $c['name'], '#url' => Url::fromUserInput($c['url'])], 'data-label' => t('Country')],
      ['data' => $c['region'], 'data-label' => t('Region')],
      ['data' => (string) $c['published'], 'data-label' => t('Published commitments')],
    ], $countries);

    $path = \Drupal::service('extension.list.module')->getPath('camh_tracker');
    $map_label = t('Map of the world. @n countries are tracked; @p have a published commitment. The table below lists the same countries.', ['@n' => count($countries), '@p' => count(array_filter($countries, fn($c) => $c['published'] > 0))]);

    return [
      '#attached' => [
        'library' => ['camh_tracker/overview'],
        'drupalSettings' => ['camhOverview' => [
          'geojson' => '/' . $path . '/data/world-countries.geojson',
          'countries' => $countries,
          'text' => [
            'published' => (string) t('published commitments'),
            'none' => (string) t('No published commitments yet'),
            'open' => (string) t('Open the country page'),
          ],
        ]],
      ],
      '#cache' => ['tags' => ['node_list'], 'contexts' => ['languages:language_content', 'user.permissions']],
      'intro' => ['#markup' => '<p class="overview-intro">' . t('A prototype tracker of public commitments on child and adolescent mental health (CAMH), with context indicators. Every record shows its source and licence.') . '</p>'],
      'kpis' => ['#markup' => '<section aria-labelledby="kpi-heading"><h2 id="kpi-heading" class="visually-hidden">' . t('Key figures') . '</h2><div class="kpi-row">' . $tiles_markup . '</div></section>'],
      'map' => [
        '#type' => 'container', '#attributes' => ['class' => ['overview-map-section']],
        'heading' => ['#markup' => '<h2>' . t('Where the tracker has data') . '</h2>'],
        'legend' => ['#markup' => '<ul class="map-legend"><li><span class="swatch swatch--published"></span> ' . t('Large dark marker: has a published commitment') . '</li><li><span class="swatch swatch--tracked"></span> ' . t('Small light marker: tracked, none published yet') . '</li><li><span class="swatch swatch--other"></span> ' . t('Not tracked') . '</li></ul>'],
        'canvas' => ['#type' => 'html_tag', '#tag' => 'div', '#attributes' => ['id' => 'overview-map', 'class' => ['overview-map'], 'role' => 'group', 'aria-label' => $map_label]],
        'note' => ['#markup' => '<p class="source-note">' . t('Country outlines: Natural Earth (public domain). Borders are generalised and imply no opinion on legal status. The table below has the same information as the map.') . '</p>'],
      ],
      'countries' => [
        '#type' => 'container', '#attributes' => ['class' => ['overview-countries']],
        'heading' => ['#markup' => '<h2>' . t('Countries at a glance') . '</h2>'],
        'table' => ['#type' => 'table', '#caption' => t('Tracked countries'), '#header' => [t('Country'), t('Region'), t('Published commitments')], '#rows' => $country_rows],
      ],
      'progress' => [
        '#type' => 'container', '#attributes' => ['class' => ['overview-progress']],
        'heading' => ['#markup' => '<h2>' . t('Progress summary') . '</h2>'],
        'table' => ['#type' => 'table', '#caption' => t('Published commitments by status'), '#header' => [t('Status'), t('Published commitments')], '#rows' => $rows],
        'note' => ['#markup' => '<p class="source-note">' . t('Only published commitments are counted. Statuses are assessed from public sources, not from opinion.') . '</p>'],
      ],
      'recent' => [
        '#type' => 'container', '#attributes' => ['class' => ['overview-recent']],
        'heading' => ['#markup' => '<h2>' . t('Recent updates') . '</h2>'],
        'table' => ['#type' => 'table', '#caption' => t('Latest published progress updates'), '#header' => [t('Date'), t('Country'), t('Commitment'), t('Status'), t('Summary')], '#rows' => $update_rows, '#empty' => t('No progress updates have been published yet.')],
      ],
    ];
  }

}
