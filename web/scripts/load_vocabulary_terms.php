<?php

/**
 * @file
 * Loads the terms of the fixed lists (themes, statuses, regions, licences, ...)
 * from data/vocabularies.csv, with their Spanish translations (AI-assisted,
 * pending human review). The lists themselves are part of the exported
 * configuration; their terms are content, so a new site needs this script.
 *
 * Run from web/:  ddev drush php:script scripts/load_vocabulary_terms.php
 * Safe to run again.
 */

use Drupal\taxonomy\Entity\Term;

$fh = fopen('/var/www/data/vocabularies.csv', 'r');
$head = fgetcsv($fh, 0, ',', '"', '\\');
$storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$created = 0;
$skipped = 0;
while (($r = fgetcsv($fh, 0, ',', '"', '\\')) !== FALSE) {
  if (count($r) !== count($head)) {
    continue;
  }
  $row = array_combine($head, $r);
  if (!\Drupal\taxonomy\Entity\Vocabulary::load($row['vocabulary'])) {
    $skipped++;
    continue;
  }
  if ($storage->loadByProperties(['vid' => $row['vocabulary'], 'field_term_code' => $row['term_code']])) {
    continue;
  }
  $term = Term::create(['vid' => $row['vocabulary'], 'name' => $row['label_en'], 'field_term_code' => $row['term_code'],
    'weight' => (int) $row['sort_order'], 'langcode' => 'en']);
  $term->save();
  $term->addTranslation('es', ['name' => $row['label_es']])->save();
  $created++;
}
fclose($fh);
print "Terms created: $created (rows for lists that do not exist: $skipped).\n";
