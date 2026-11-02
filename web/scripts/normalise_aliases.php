<?php

/**
 * @file
 * Makes every friendly web address come from the English title (or, for
 * commitments, from the slug in data/commitments.csv), whatever order the
 * records and their Spanish translations were saved in.
 *
 * Saving a translation can make the address generator rebuild an address from
 * the Spanish name (for example /countries/kenia). This script runs last, keeps
 * exactly one address per record, and saves it for all languages.
 *
 * Run from web/:  ddev drush php:script scripts/normalise_aliases.php
 * Safe to run again.
 */

use Drupal\path_alias\Entity\PathAlias;

$cleaner = \Drupal::service('pathauto.alias_cleaner');
$aliases = \Drupal::entityTypeManager()->getStorage('path_alias');
$nodes = \Drupal::entityTypeManager()->getStorage('node');

// Slugs for commitments come from the CSV (by English title).
$slugs = [];
$fh = fopen('/var/www/data/commitments.csv', 'r');
$head = fgetcsv($fh, 0, ',', '"', '\\');
while (($r = fgetcsv($fh, 0, ',', '"', '\\')) !== FALSE) {
  if (count($r) === count($head)) {
    $row = array_combine($head, $r);
    $slugs[$row['title_en']] = $row['slug'];
  }
}
fclose($fh);

$prefix = ['country' => 'countries', 'institution' => 'institutions', 'indicator' => 'indicators', 'document' => 'documents', 'commitment' => 'commitments'];
$fixed = 0;
foreach ($prefix as $bundle => $dir) {
  foreach ($nodes->loadMultiple($nodes->getQuery()->accessCheck(FALSE)->condition('type', $bundle)->execute()) as $node) {
    $title = $node->getUntranslated()->label();
    $slug = $bundle === 'commitment' ? ($slugs[$title] ?? NULL) : $cleaner->cleanString($title);
    if (!$slug) {
      continue;
    }
    $desired = "/$dir/$slug";
    $path = '/node/' . $node->id();
    $have = FALSE;
    foreach ($aliases->loadByProperties(['path' => $path]) as $a) {
      if ($a->getAlias() === $desired) {
        $have = TRUE;
        if ($a->language()->getId() !== 'und') {
          $a->set('langcode', 'und')->save();
          $fixed++;
        }
      }
      else {
        $a->delete();
        $fixed++;
      }
    }
    if (!$have) {
      PathAlias::create(['path' => $path, 'alias' => $desired, 'langcode' => 'und'])->save();
      $fixed++;
    }
  }
}
print "Web addresses normalised ($fixed changes).\n";
