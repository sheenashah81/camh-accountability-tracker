<?php

/**
 * @file
 * Prepares a blank site so that the exported configuration can be imported.
 *
 * The footer block in the exported configuration points at a content block by
 * its UUID. Content is not part of configuration, so this script creates that
 * block with the same UUID before the import. Used by .github/workflows/ci.yml.
 *
 * Run from web/:  ddev drush php:script scripts/ci_prepare.php
 */

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Config\FileStorage;
use Symfony\Component\Yaml\Yaml;

// A freshly installed site gives its standard configuration (block types, text
// formats, ...) new random UUIDs. Align them with the exported configuration so
// that the import updates those items instead of trying to delete and recreate them.
$sync = new FileStorage('/var/www/html/config/sync');
$aligned = 0;
foreach ($sync->listAll() as $name) {
  $data = $sync->read($name);
  if (!is_array($data) || empty($data['uuid']) || $name === 'system.site') {
    continue;
  }
  $active = \Drupal::configFactory()->getEditable($name);
  if (!$active->isNew() && $active->get('uuid') !== $data['uuid']) {
    $active->set('uuid', $data['uuid'])->save();
    $aligned++;
  }
}
print "Aligned $aligned configuration UUIDs.\n";

$block = Yaml::parseFile('/var/www/html/config/sync/block.block.camh_theme_footer_disclaimer.yml');
$uuid = str_replace('block_content:', '', $block['plugin']);
if (!\Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['uuid' => $uuid])) {
  BlockContent::create([
    'type' => 'basic', 'uuid' => $uuid, 'info' => 'Footer disclaimer', 'reusable' => TRUE,
    'body' => ['format' => 'basic_html', 'value' =>
      '<p><strong>Independent portfolio prototype.</strong> Not affiliated with, endorsed by or connected to UNICEF, WHO, DevInfo, DHIS2 or any government. '
      . 'Commitments and indicators come from cited public documents and datasets; every record shows its source, licence and the date it was checked. '
      . 'Records marked Draft are not yet reviewed. Spanish text is AI-assisted and has been reviewed by a person.</p>'],
  ])->save();
}
print "Footer block ready ($uuid).\n";
