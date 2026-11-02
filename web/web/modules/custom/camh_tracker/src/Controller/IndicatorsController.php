<?php

namespace Drupal\camh_tracker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * The Indicators page: each indicator compared across the tracked countries.
 *
 * One bar chart per indicator (latest value for each country, one colour, no
 * legend needed because the countries are labelled), with a data table, the
 * plain-language definition and the source and licence.
 */
class IndicatorsController extends ControllerBase {

  public function build(): array {
    $nodes = $this->entityTypeManager()->getStorage('node');
    $repo = \Drupal::service('entity.repository');
    $date = \Drupal::service('date.formatter');
    $settings = [];
    $sections = [];
    $ids = $nodes->getQuery()->accessCheck(TRUE)->condition('type', 'indicator')->condition('status', 1)->sort('title')->execute();
    foreach ($nodes->loadMultiple($ids) as $ind) {
      $ind = $repo->getTranslationFromContext($ind);
      $unit = $ind->get('field_unit')->value;
      // Latest value per country.
      $latest = [];
      $vids = $nodes->getQuery()->accessCheck(TRUE)->condition('type', 'indicator_value')->condition('status', 1)
        ->condition('field_indicator', $ind->id())->sort('field_year', 'DESC')->execute();
      $source = NULL;
      foreach ($nodes->loadMultiple($vids) as $v) {
        $country = $v->get('field_country')->entity;
        $iso = $country ? $country->get('field_iso3')->value : NULL;
        if (!$iso || isset($latest[$iso])) {
          continue;
        }
        $country = $repo->getTranslationFromContext($country);
        $latest[$iso] = ['name' => $country->label(), 'url' => $country->toUrl()->toString(), 'year' => (int) $v->get('field_year')->value, 'value' => (float) $v->get('field_value')->value];
        $source ??= $v;
      }
      if (!$latest) {
        continue;
      }
      uasort($latest, fn($a, $b) => $b['value'] <=> $a['value']);
      $fmt = fn($x) => camh_tracker_format_number($x, $x >= 1000 ? 0 : 2);
      $rows = [];
      foreach ($latest as $row) {
        $rows[] = [
          ['data' => ['#type' => 'link', '#title' => $row['name'], '#url' => Url::fromUserInput($row['url'])], 'data-label' => t('Country')],
          ['data' => (string) $row['year'], 'data-label' => t('Latest year')],
          ['data' => $fmt($row['value']), 'data-label' => t('Latest value')],
        ];
      }
      $key = 'i' . $ind->id();
      $settings[$key] = ['unit' => $unit, 'rows' => array_values($latest)];
      $licence = $source->get('field_licence')->entity;
      $licence = $licence ? $repo->getTranslationFromContext($licence)->label() : '';
      $top = reset($latest);
      $bottom = end($latest);
      $summary = t('Bar chart: latest @name by country, from @low (@lc) to @high (@hc) @unit. The table below lists every value.', [
        '@name' => $ind->label(), '@low' => $fmt($bottom['value']), '@lc' => $bottom['name'], '@high' => $fmt($top['value']), '@hc' => $top['name'], '@unit' => $unit,
      ]);
      $sections[$key] = [
        '#type' => 'container', '#attributes' => ['class' => ['indicator-compare']],
        'heading' => ['#markup' => '<h2>' . htmlspecialchars($ind->label()) . '</h2>'],
        'definition' => ['#markup' => '<p class="indicator-definition-full">' . strip_tags((string) $ind->get('field_definition')->value) . '</p>'],
        'chart' => ['#type' => 'html_tag', '#tag' => 'div', '#attributes' => ['class' => ['indicator-bars'], 'style' => 'height:' . max(240, count($rows) * 44 + 60) . 'px', 'data-key' => $key, 'role' => 'img', 'aria-label' => $summary, 'tabindex' => 0]],
        'table' => ['#type' => 'table', '#caption' => t('Latest value by country (@unit)', ['@unit' => $unit]), '#header' => [t('Country'), t('Latest year'), t('Latest value')], '#rows' => $rows, '#attributes' => ['class' => ['indicator-compare-table']]],
        'source' => ['#markup' => '<p class="source-note">' . t('Source: @s. Licence: @l. Retrieved @d.', [
          '@s' => $source->get('field_source_name')->value, '@l' => $licence,
          '@d' => $date->format(strtotime($source->get('field_retrieved_on')->value), 'camh_date'),
        ]) . ' ' . t('Values are the latest year available for each country; open a country page to see the full trend.') . '</p>'],
      ];
    }
    return [
      '#attached' => ['library' => ['camh_tracker/indicators'], 'drupalSettings' => ['camhIndicators' => $settings]],
      '#cache' => ['tags' => ['node_list'], 'contexts' => ['languages:language_content']],
      'intro' => ['#markup' => '<p class="overview-intro">' . t('Context indicators from public datasets, compared across the countries this prototype tracks. They show the setting commitments are made in; they are not measures of whether a commitment is working.') . '</p>'],
    ] + $sections;
  }

}
