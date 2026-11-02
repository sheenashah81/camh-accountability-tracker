<?php

/**
 * @file
 * Makes camh_theme the default theme and places its blocks: branding, main
 * menu, language switcher, breadcrumb, page title, messages, main content and
 * the footer disclaimer.
 *
 * Run from web/:  ddev drush php:script scripts/activate_theme.php
 * Safe to run again.
 */

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;

$theme = 'camh_theme';
\Drupal::service('theme_installer')->install([$theme]);
\Drupal::configFactory()->getEditable('system.theme')->set('default', $theme)->save();

// Footer disclaimer as a content block.
$existing = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['info' => 'Footer disclaimer']);
if ($existing) {
  $footer = reset($existing);
}
else {
  $footer = BlockContent::create([
    'type' => 'basic', 'info' => 'Footer disclaimer', 'reusable' => TRUE,
    'body' => ['format' => 'basic_html', 'value' =>
      '<p><strong>Independent portfolio prototype.</strong> Not affiliated with, endorsed by or connected to UNICEF, WHO, DevInfo, DHIS2 or any government. '
      . 'Commitments and indicators come from cited public documents and datasets; every record shows its source, licence and the date it was checked. '
      . 'Records marked Draft are not yet reviewed. Spanish text is AI-assisted and has been reviewed by a person.</p>'],
  ]);
  $footer->save();
}

$placements = [
  'branding' => ['system_branding_block', 'nav_branding', 0, ['use_site_logo' => FALSE, 'use_site_name' => TRUE, 'use_site_slogan' => FALSE]],
  'main_menu' => ['system_menu_block:main', 'nav_main', 0, ['level' => 1, 'depth' => 1, 'expand_all_items' => FALSE]],
  'language_switcher' => ['language_block:language_interface', 'nav_additional', 0, []],
  'breadcrumbs' => ['system_breadcrumb_block', 'breadcrumb', 0, []],
  'page_title' => ['page_title_block', 'content', -10, []],
  'messages' => ['system_messages_block', 'content', -9, []],
  'local_tasks' => ['local_tasks_block', 'content', -8, ['primary' => TRUE, 'secondary' => TRUE]],
  'main_content' => ['system_main_block', 'content', 0, []],
  'footer_disclaimer' => ['block_content:' . $footer->uuid(), 'footer', 0, []],
];
$log = [];
foreach ($placements as $key => [$plugin, $region, $weight, $settings]) {
  $id = "{$theme}_$key";
  if ($old = Block::load($id)) {
    $old->delete();
  }
  Block::create([
    'id' => $id, 'theme' => $theme, 'region' => $region, 'weight' => $weight, 'plugin' => $plugin,
    'settings' => $settings + ['id' => $plugin, 'label' => ucfirst(str_replace('_', ' ', $key)), 'label_display' => FALSE, 'provider' => 'system'],
    'visibility' => [],
  ])->save();
  $log[] = $id;
}

// The standard profile already provides a Home link in the main menu, so none is added here.
print 'Theme active. Blocks: ' . implode(', ', $log) . "\n";
