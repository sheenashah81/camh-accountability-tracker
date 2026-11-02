<?php

/**
 * @file
 * Build pass 2b: user roles and the first data load.
 *
 * Creates the Contributor and Editor roles and loads the cited data from
 * data/*.csv (countries, institutions, commitments, indicators, indicator
 * values) through the validation rules in the camh_tracker module. Rows that
 * break a rule are skipped and reported. Safe to run again: existing records
 * are skipped.
 *
 * Run from the web/ folder:  ddev drush php:script scripts/build_content_model_3.php
 */

use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;

$log = [];
// Run as the administrator so that workflow transitions and text formats are allowed.
\Drupal::service('account_switcher')->switchTo(\Drupal\user\Entity\User::load(1));
$etm = \Drupal::entityTypeManager();

// 1. Roles. Administrator already exists in the standard profile.
$available = array_keys(\Drupal::service('user.permissions')->getPermissions());
$grant = function (string $rid, string $label, array $perms) use ($available, &$log) {
  $role = Role::load($rid) ?: Role::create(['id' => $rid, 'label' => $label]);
  $skipped = [];
  foreach ($perms as $p) {
    if (in_array($p, $available, TRUE)) {
      $role->grantPermission($p);
    }
    else {
      $skipped[] = $p;
    }
  }
  $role->save();
  $log[] = "role $rid saved" . ($skipped ? ' (unknown permissions skipped: ' . implode(', ', $skipped) . ')' : '');
};
$own = ['commitment', 'milestone', 'progress_update'];
$contrib = ['access content', 'view own unpublished content', 'access toolbar', 'view the administration theme',
  'access content overview',
  'use commitment_review transition create_new_draft', 'use commitment_review transition submit_for_review'];
foreach ($own as $t) {
  $contrib[] = "create $t content";
  $contrib[] = "edit own $t content";
}
$grant('contributor', 'Contributor', $contrib);
$editor = ['access content', 'access content overview', 'view any unpublished content', 'view all revisions', 'access toolbar',
  'view the administration theme', 'create content translations', 'update content translations', 'translate any entity',
  'use commitment_review transition create_new_draft', 'use commitment_review transition submit_for_review',
  'use commitment_review transition reject', 'use commitment_review transition approve_and_publish',
  'use commitment_review transition back_to_draft'];
foreach (['commitment', 'milestone', 'progress_update', 'country', 'institution', 'indicator', 'indicator_value', 'document'] as $t) {
  $editor[] = "create $t content";
  $editor[] = "edit any $t content";
}
$grant('editor', 'Editor', $editor);

// 2. Helpers.
function camh_csv(string $name): array {
  $fh = fopen("/var/www/data/$name", 'r');
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
function camh_term(string $vid, string $label): ?int {
  static $cache = [];
  if (!isset($cache[$vid])) {
    foreach (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid]) as $t) {
      $cache[$vid][$t->label()] = (int) $t->id();
    }
  }
  return $cache[$vid][$label] ?? NULL;
}
function camh_node_id(string $type, string $field, string $value): ?int {
  $ids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()->accessCheck(FALSE)
    ->condition('type', $type)->condition($field, $value)->range(0, 1)->execute();
  return $ids ? (int) reset($ids) : NULL;
}
function camh_save(Node $node, string $key, array &$counts, array &$problems): ?Node {
  $violations = $node->validate();
  if (count($violations)) {
    $msgs = [];
    foreach ($violations as $v) {
      $msgs[] = $v->getPropertyPath() . ': ' . strip_tags((string) $v->getMessage());
    }
    $problems[] = "$key -> " . implode(' | ', $msgs);
    $counts['skipped']++;
    return NULL;
  }
  $node->save();
  $counts['created']++;
  return $node;
}
function camh_state(Node $node, string $state): void {
  foreach (array_keys($node->getTranslationLanguages()) as $lang) {
    $node->getTranslation($lang)->set('moderation_state', $state);
  }
}
$blank = fn(?string $v) => ($v === NULL || trim($v) === '') ? NULL : trim($v);
$date = fn(?string $v) => ($v === NULL || trim($v) === '') ? NULL : trim($v);
$problems = [];

// 3. Countries.
$c = ['created' => 0, 'skipped' => 0, 'existing' => 0];
foreach (camh_csv('countries.csv') as $r) {
  if (camh_node_id('country', 'field_iso3', $r['iso3'])) {
    $c['existing']++;
    continue;
  }
  $node = Node::create(['type' => 'country', 'title' => $r['name_en'], 'langcode' => 'en', 'uid' => 1, 'status' => 1,
    'field_iso3' => $r['iso3'], 'field_region' => camh_term('region', $r['region']),
    'field_summary' => $blank($r['summary']), 'field_is_starter' => $r['is_starter'] === 'true' ? 1 : 0]);
  if ($saved = camh_save($node, "country {$r['iso3']}", $c, $problems)) {
    $saved->addTranslation('es', ['title' => $r['name_es']])->save();
  }
}
$log[] = 'countries: ' . json_encode($c);

// 4. Institutions.
$c = ['created' => 0, 'skipped' => 0, 'existing' => 0];
foreach (camh_csv('institutions.csv') as $r) {
  if (camh_node_id('institution', 'field_code', $r['code'])) {
    $c['existing']++;
    continue;
  }
  $node = Node::create(['type' => 'institution', 'title' => $r['name_en'], 'langcode' => 'en', 'uid' => 1, 'status' => 1,
    'field_code' => $r['code'], 'field_country' => camh_node_id('country', 'field_iso3', $r['country_iso3']),
    'field_institution_type' => camh_term('institution_type', $r['institution_type'])]);
  if ($w = $blank($r['website'])) {
    $node->set('field_website', ['uri' => $w]);
  }
  if ($saved = camh_save($node, "institution {$r['code']}", $c, $problems)) {
    $saved->addTranslation('es', ['title' => $r['name_es']])->save();
  }
}
$log[] = 'institutions: ' . json_encode($c);

// 5. Commitments (all loaded as Draft).
$c = ['created' => 0, 'skipped' => 0, 'existing' => 0];
foreach (camh_csv('commitments.csv') as $r) {
  $country = camh_node_id('country', 'field_iso3', $r['country_iso3']);
  $exists = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'commitment', 'title' => $r['title_en'], 'field_country' => $country]);
  if ($exists) {
    $c['existing']++;
    continue;
  }
  $themes = array_filter(array_map(fn($t) => camh_term('theme', $t), explode('|', $r['theme'])));
  $node = Node::create(['type' => 'commitment', 'title' => $r['title_en'], 'langcode' => 'en', 'uid' => 1, 'status' => 0,
    'field_country' => $country, 'field_institution' => camh_node_id('institution', 'field_code', $r['institution_code']),
    'field_theme' => array_map(fn($id) => ['target_id' => $id], $themes),
    'field_commitment_type' => camh_term('commitment_type', $r['commitment_type']),
    'field_description' => ['value' => $r['description_en'], 'format' => 'basic_html'],
    'field_date_made' => $date($r['date_made']), 'field_target_date' => $date($r['target_date']),
    'field_status' => $blank($r['status']) ? camh_term('status', $r['status']) : NULL,
    'field_status_updated_on' => $date($r['status_updated_on']),
    'field_source_name' => $r['source_name'], 'field_licence' => camh_term('licence', $r['licence']),
    'field_verified_on' => $date($r['verified_on']), 'field_is_illustrative' => $r['is_illustrative'] === 'true' ? 1 : 0,
    'field_verification_status' => $blank($r['verification_status']),
    'field_verification_note' => ['value' => $r['verification_note'], 'format' => 'plain_text'],
    'field_es_review_status' => $blank($r['description_es']) ? 'reviewed' : 'pending',
    'path' => ['alias' => '/commitments/' . $r['slug'], 'pathauto' => 0],
    'moderation_state' => 'draft']);
  if ($u = $blank($r['source_url'])) {
    $node->set('field_source_url', ['uri' => $u]);
  }
  // Spanish version (AI-assisted, pending review) is created with the original so
  // that it starts in the same moderation state and is never public by accident.
  if ($blank($r['title_es']) && $blank($r['description_es'])) {
    $node->addTranslation('es', ['title' => $r['title_es'],
      'field_description' => ['value' => $r['description_es'], 'format' => 'basic_html']]);
    camh_state($node, 'draft');
  }
  if ($saved = camh_save($node, "commitment {$r['country_iso3']}", $c, $problems)) {
    if ($r['workflow_state'] === 'Published') {
      foreach (['in_review', 'published'] as $state) {
        camh_state($saved, $state);
        $saved->setNewRevision(TRUE);
        $saved->setRevisionLogMessage("Loaded from data/commitments.csv; review step: $state");
        $saved->set('path', ['alias' => '/commitments/' . $r['slug'], 'pathauto' => \Drupal\pathauto\PathautoState::SKIP]);
        $saved->save();
      }
    }
  }
}
$log[] = 'commitments: ' . json_encode($c);

// 6. Indicators.
$c = ['created' => 0, 'skipped' => 0, 'existing' => 0];
foreach (camh_csv('indicators.csv') as $r) {
  if (camh_node_id('indicator', 'field_code', $r['code'])) {
    $c['existing']++;
    continue;
  }
  $node = Node::create(['type' => 'indicator', 'title' => $r['name_en'], 'langcode' => 'en', 'uid' => 1, 'status' => 1,
    'field_code' => $r['code'], 'field_definition' => ['value' => $r['definition_en'], 'format' => 'basic_html'],
    'field_unit' => $r['unit'], 'field_theme' => camh_term('theme', $r['theme']),
    'field_direction' => camh_term('indicator_direction', $r['direction']), 'field_age_group' => $r['age_group']]);
  if ($saved = camh_save($node, "indicator {$r['code']}", $c, $problems)) {
    $saved->addTranslation('es', ['title' => $r['name_es'],
      'field_definition' => ['value' => $r['definition_es'], 'format' => 'basic_html']])->save();
  }
}
$log[] = 'indicators: ' . json_encode($c);

// 7. Indicator values.
$c = ['created' => 0, 'skipped' => 0, 'existing' => 0];
$country_ids = [];
$indicator_ids = [];
foreach (camh_csv('indicator_values.csv') as $r) {
  $country_ids[$r['country_iso3']] ??= camh_node_id('country', 'field_iso3', $r['country_iso3']);
  $indicator_ids[$r['indicator_code']] ??= camh_node_id('indicator', 'field_code', $r['indicator_code']);
  if (!$indicator_ids[$r['indicator_code']] || !$country_ids[$r['country_iso3']]) {
    $c['skipped']++;
    continue;
  }
  $cond = ['type' => 'indicator_value', 'field_indicator' => $indicator_ids[$r['indicator_code']],
    'field_country' => $country_ids[$r['country_iso3']], 'field_year' => (int) $r['year']];
  if ($r['sex'] !== '') {
    $cond['field_sex'] = $r['sex'];
  }
  if ($etm->getStorage('node')->loadByProperties($cond)) {
    $c['existing']++;
    continue;
  }
  $node = Node::create(['type' => 'indicator_value', 'langcode' => 'en', 'uid' => 1, 'status' => 1,
    'title' => "{$r['indicator_code']} | {$r['country_iso3']} | {$r['year']} | {$r['sex']}",
    'field_indicator' => $indicator_ids[$r['indicator_code']], 'field_country' => $country_ids[$r['country_iso3']],
    'field_year' => (int) $r['year'], 'field_value' => $r['value'], 'field_sex' => $r['sex'],
    'field_notes' => $r['notes'], 'field_source_name' => $r['source_name'],
    'field_source_url' => ['uri' => $r['source_url']], 'field_licence' => camh_term('licence', $r['licence']),
    'field_retrieved_on' => $r['retrieved_on'], 'field_is_illustrative' => $r['is_illustrative'] === 'true' ? 1 : 0]);
  camh_save($node, "value {$r['indicator_code']} {$r['country_iso3']} {$r['year']}", $c, $problems);
}
$log[] = 'indicator values: ' . json_encode($c);

print implode("\n", $log) . "\n";
if ($problems) {
  print "PROBLEMS (" . count($problems) . "):\n" . implode("\n", array_slice($problems, 0, 25)) . "\n";
}
print "Done.\n";
