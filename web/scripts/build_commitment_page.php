<?php

/**
 * @file
 * Lays out the public commitment page and loads the milestone and progress
 * update records from data/milestones.csv and data/progress_updates.csv.
 *
 * Run from web/:  ddev drush php:script scripts/build_commitment_page.php
 * Safe to run again.
 */

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\node\Entity\Node;

if (!\Drupal\Core\Datetime\Entity\DateFormat::load('camh_date')) {
  \Drupal\Core\Datetime\Entity\DateFormat::create(['id' => 'camh_date', 'label' => 'Tracker date (1 July 2025)', 'pattern' => 'j F Y'])->save();
}
\Drupal::service('account_switcher')->switchTo(\Drupal\user\Entity\User::load(1));

// 1. Page layout: order, labels and hidden internal fields.
$display = EntityViewDisplay::load('node.commitment.default');
$show = [
  'record_notices' => [-20, NULL],
  'field_country' => [1, 'inline'],
  'field_institution' => [2, 'inline'],
  'field_commitment_type' => [3, 'inline'],
  'field_theme' => [4, 'inline'],
  'field_status' => [5, 'inline'],
  'field_status_updated_on' => [6, 'inline'],
  'field_date_made' => [7, 'inline'],
  'field_target_date' => [8, 'inline'],
  'field_description' => [10, 'hidden'],
  'progress_updates' => [20, NULL],
  'milestones' => [21, NULL],
  'provenance' => [30, NULL],
];
foreach ($display->getComponents() as $name => $c) {
  if (!isset($show[$name]) && !in_array($name, ['links'], TRUE)) {
    $display->removeComponent($name);
  }
}
foreach ($show as $name => [$weight, $label]) {
  $component = $display->getComponent($name) ?: [];
  if (!$component && str_starts_with($name, 'field_')) {
    continue;
  }
  $component['weight'] = $weight;
  if (in_array($name, ['field_date_made', 'field_target_date', 'field_status_updated_on'], TRUE)) {
    $component['settings']['format_type'] = 'camh_date';
  }
  if ($name === 'field_status') {
    $component['type'] = 'camh_status_badge';
    $component['settings'] = [];
  }
  if ($label) {
    $component['label'] = $label;
  }
  $display->setComponent($name, $component);
}
$display->save();

// 2. Load milestones and progress updates.
function camh_rows(string $file): array {
  $fh = fopen("/var/www/data/$file", 'r');
  $head = fgetcsv($fh, 0, ',', '"', '\\');
  $rows = [];
  while (($r = fgetcsv($fh, 0, ',', '"', '\\')) !== FALSE) {
    if (count($r) === count($head)) {
      $rows[] = array_combine($head, $r);
    }
  }
  fclose($fh);
  return $rows;
}
$term = function (string $vid, string $name) {
  foreach (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid, 'name' => $name]) as $t) {
    return (int) $t->id();
  }
};
$commitment_by_slug = function (string $slug) {
  $alias = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties(['alias' => '/commitments/' . $slug]);
  $alias = reset($alias);
  return $alias ? (int) str_replace('/node/', '', $alias->getPath()) : NULL;
};
$log = [];
$problems = [];
foreach (camh_rows('milestones.csv') as $r) {
  $cid = $commitment_by_slug($r['commitment_slug']);
  $exists = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'milestone', 'title' => $r['title_en'], 'field_commitment' => $cid]);
  if ($exists || !$cid) {
    continue;
  }
  $node = Node::create(['type' => 'milestone', 'title' => $r['title_en'], 'langcode' => 'en', 'uid' => 1, 'status' => 1,
    'field_commitment' => $cid, 'field_due_date' => $r['due_date'], 'field_completed_on' => $r['completed_on'] ?: NULL,
    'field_milestone_status' => $term('status', $r['milestone_status']), 'field_is_illustrative' => $r['is_illustrative'] === 'true' ? 1 : 0]);
  $v = $node->validate();
  if (count($v)) {
    foreach ($v as $x) {
      $problems[] = 'milestone: ' . $x->getMessage();
    }
    continue;
  }
  if (!empty($r['title_es'])) {
    $node->addTranslation('es', ['title' => $r['title_es']]);
  }
  $node->save();
  $log[] = 'milestone created';
}
foreach (camh_rows('progress_updates.csv') as $r) {
  $cid = $commitment_by_slug($r['commitment_slug']);
  $exists = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'progress_update', 'field_commitment' => $cid, 'field_update_date' => $r['update_date']]);
  if ($exists || !$cid) {
    continue;
  }
  $node = Node::create(['type' => 'progress_update', 'title' => 'Progress update ' . $r['update_date'] . ' (' . $r['commitment_slug'] . ')',
    'langcode' => 'en', 'uid' => 1, 'status' => 0,
    'field_commitment' => $cid, 'field_update_date' => $r['update_date'],
    'field_summary' => ['value' => $r['summary_en'], 'format' => 'basic_html'], 'field_new_status' => $term('status', $r['new_status']),
    'field_source_url' => $r['source_url'] ? ['uri' => $r['source_url']] : NULL,
    'field_is_illustrative' => $r['is_illustrative'] === 'true' ? 1 : 0, 'moderation_state' => 'draft']);
  if (!empty($r['summary_es'])) {
    $node->addTranslation('es', ['title' => $node->label(), 'field_summary' => ['value' => $r['summary_es'], 'format' => 'basic_html']]);
  }
  foreach (['in_review', 'published'] as $state) {
    foreach (array_keys($node->getTranslationLanguages()) as $lang) {
      $node->getTranslation($lang)->set('moderation_state', $state);
    }
    $node->setNewRevision(TRUE);
    $node->setRevisionLogMessage("Progress update loaded from data/progress_updates.csv; review step: $state");
    $v = $node->validate();
    if (count($v)) {
      foreach ($v as $x) {
        $problems[] = 'progress update: ' . $x->getMessage();
      }
      break;
    }
    $node->save();
  }
  $log[] = 'progress update created';
}
print implode("\n", $log) . "\n" . ($problems ? "PROBLEMS:\n" . implode("\n", $problems) . "\n" : '') . "Done.\n";
