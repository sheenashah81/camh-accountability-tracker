<?php

/**
 * @file
 * Smoke test for the camh_tracker validation rules (data dictionary section 4).
 *
 * Builds deliberately invalid records in memory (nothing is saved) and checks
 * that each is rejected with the expected message. Run from the web/ folder:
 *   ddev drush php:script /var/www/tests/drupal/validation_smoke_test.php
 */

use Drupal\node\Entity\Node;

\Drupal::service('account_switcher')->switchTo(\Drupal\user\Entity\User::load(1));
$q = fn($type, $field, $value) => (int) (array_values(\Drupal::entityQuery('node')->accessCheck(FALSE)->condition('type', $type)->condition($field, $value)->execute())[0] ?? 0);
$term = function ($vid, $label) {
  foreach (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid, 'name' => $label]) as $t) {
    return (int) $t->id();
  }
};
$ken = $q('country', 'field_iso3', 'KEN');
$nga = $q('country', 'field_iso3', 'NGA');
$ken_moh = $q('institution', 'field_code', 'KEN-MOH');
$nga_fmoh = $q('institution', 'field_code', 'NGA-FMOH');
$desc = str_repeat('Plain language description of a public commitment. ', 2);

$base = fn(array $o = []) => Node::create($o + [
  'type' => 'commitment', 'title' => 'Test commitment ' . uniqid(), 'langcode' => 'en', 'status' => 0,
  'field_country' => $ken, 'field_institution' => $ken_moh, 'field_theme' => [$term('theme', 'Financing')],
  'field_commitment_type' => $term('commitment_type', 'Policy'), 'field_description' => $desc,
  'field_source_name' => 'Test source', 'field_licence' => $term('licence', 'CC BY 4.0'),
  'field_is_illustrative' => 0, 'moderation_state' => 'draft',
]);

$cases = [
  'A valid draft passes' => [$base(), NULL],
  'Duplicate country code rejected (V5)' => [Node::create(['type' => 'country', 'title' => 'Kenya 2', 'field_iso3' => 'KEN', 'field_region' => $term('region', 'East Africa'), 'field_is_starter' => 1]), 'already exists'],
  'Bad country code rejected' => [Node::create(['type' => 'country', 'title' => 'X', 'field_iso3' => 'ke', 'field_region' => $term('region', 'East Africa'), 'field_is_starter' => 1]), 'three uppercase'],
  'Institution from another country rejected (V6)' => [$base(['field_institution' => $nga_fmoh]), 'same as the commitment'],
  'Target date before date made rejected (V3)' => [$base(['field_date_made' => '2025-05-01', 'field_target_date' => '2025-01-01']), 'target date cannot be before'],
  'Description too short rejected' => [$base(['field_description' => 'Too short.']), 'between 50 and 1,200'],
  'Submit for review without a status rejected (V1)' => [$base(['moderation_state' => 'in_review', 'field_date_made' => '2025-01-01']), 'Before review, add'],
  'Publishing with an unchecked licence rejected (P1)' => [$base(['moderation_state' => 'published', 'status' => 1, 'field_date_made' => '2025-01-01', 'field_status' => $term('status', 'In progress'), 'field_source_url' => ['uri' => 'https://example.org'], 'field_verified_on' => '2025-02-01', 'field_licence' => $term('licence', 'Not yet checked')]), 'cannot be published'],
  'Rejection without a comment rejected (V9)' => [$base(['moderation_state' => 'rejected']), 'needs a review comment'],
  'Future verified-on date rejected (V3)' => [$base(['field_verified_on' => '2999-01-01']), 'cannot be in the future'],
];
$fail = 0;
foreach ($cases as $name => [$node, $expect]) {
  $msgs = [];
  foreach ($node->validate() as $v) {
    $msgs[] = strip_tags((string) $v->getMessage());
  }
  $all = implode(' | ', $msgs);
  $ok = $expect === NULL ? $all === '' : str_contains($all, $expect);
  $fail += $ok ? 0 : 1;
  print ($ok ? 'PASS  ' : 'FAIL  ') . $name . ($ok ? '' : "  (got: $all)") . "\n";
}
// Downloads contain only published records.
$exported = array_column(\Drupal::classResolver(\Drupal\camh_tracker\Controller\DataController::class)->commitmentRows(), 'title');
$drafts = [];
foreach (\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'commitment', 'status' => 0]) as $d) {
  if (in_array($d->label(), $exported, TRUE)) {
    $drafts[] = $d->id();
  }
}
$ok = !$drafts && count($exported) === (int) \Drupal::entityQuery('node')->accessCheck(FALSE)->condition('type', 'commitment')->condition('status', 1)->count()->execute();
$fail += $ok ? 0 : 1;
print ($ok ? 'PASS  ' : 'FAIL  ') . 'Downloads contain only published commitments' . ($ok ? '' : '  (draft ids exported: ' . implode(', ', $drafts) . ')') . "\n";

// Publication guard: no translation may be public when its original is not.
$leaks = [];
foreach (\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => ['commitment', 'progress_update']]) as $n) {
  foreach ($n->getTranslationLanguages(FALSE) as $lang => $l) {
    if ($n->getTranslation($lang)->isPublished() && !$n->getUntranslated()->isPublished()) {
      $leaks[] = $n->id() . ' (' . $lang . ')';
    }
  }
}
$ok = !$leaks;
$fail += $ok ? 0 : 1;
print ($ok ? 'PASS  ' : 'FAIL  ') . 'No translation is public while its original is unpublished' . ($ok ? '' : '  (leaking: ' . implode(', ', $leaks) . ')') . "\n";
print $fail ? "$fail FAILED\n" : "All checks passed.\n";
