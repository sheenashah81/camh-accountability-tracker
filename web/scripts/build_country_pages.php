<?php

/**
 * @file
 * Lays out the public country profile and creates the Countries list
 * (/en/tracker/countries).
 *
 * Run from web/:  ddev drush php:script scripts/build_country_pages.php
 * Re-running replaces the view.
 */

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\views\Entity\View;

// 1. Country profile layout.
$display = EntityViewDisplay::load('node.country.default');
$show = [
  'field_region' => [1, 'inline'], 'field_summary' => [2, 'hidden'],
  'country_commitments' => [20, NULL], 'country_indicators' => [21, NULL],
];
foreach ($display->getComponents() as $name => $c) {
  if (!isset($show[$name]) && $name !== 'links') {
    $display->removeComponent($name);
  }
}
foreach ($show as $name => [$weight, $label]) {
  $component = $display->getComponent($name) ?: [];
  if (!$component && str_starts_with($name, 'field_')) {
    continue;
  }
  $component['weight'] = $weight;
  if ($label) {
    $component['label'] = $label;
  }
  $display->setComponent($name, $component);
}
$display->save();

// 2. Countries list.
if ($old = View::load('countries')) {
  $old->delete();
}
$field = fn(string $id, string $table, string $name, string $label, string $type, array $settings, array $extra = []) => $extra + [
  'id' => $id, 'table' => $table, 'field' => $name, 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => '',
  'label' => $label, 'exclude' => FALSE, 'alter' => ['alter_text' => FALSE, 'make_link' => FALSE, 'absolute' => FALSE, 'word_boundary' => FALSE, 'ellipsis' => FALSE, 'strip_tags' => FALSE, 'trim' => FALSE, 'html' => FALSE],
  'element_type' => '', 'element_class' => '', 'element_label_type' => '', 'element_label_class' => '', 'element_label_colon' => FALSE,
  'element_wrapper_type' => '', 'element_wrapper_class' => '', 'element_default_classes' => TRUE,
  'empty' => '', 'hide_empty' => FALSE, 'empty_zero' => FALSE, 'hide_alter_empty' => TRUE,
  'click_sort_column' => 'value', 'type' => $type, 'settings' => $settings, 'group_column' => 'value', 'group_columns' => [],
  'group_rows' => TRUE, 'delta_limit' => 0, 'delta_offset' => 0, 'delta_reversed' => FALSE, 'delta_first_last' => FALSE,
  'multi_type' => 'separator', 'separator' => ', ', 'field_api_classes' => FALSE, 'plugin_id' => 'field',
];
$col = ['sortable' => TRUE, 'default_sort_order' => 'asc', 'align' => '', 'separator' => '', 'empty_column' => FALSE, 'responsive' => ''];
$opts = [
  'title' => 'Countries',
  'fields' => [
    'title' => $field('title', 'node_field_data', 'title', 'Country', 'string', ['link_to_entity' => TRUE], ['entity_type' => 'node', 'entity_field' => 'title']),
    'name' => $field('name', 'taxonomy_term_field_data', 'name', 'Region', 'string', ['link_to_entity' => FALSE], ['entity_type' => 'taxonomy_term', 'entity_field' => 'name', 'relationship' => 'field_region']),
  ],
  'relationships' => [
    'field_region' => ['id' => 'field_region', 'table' => 'node__field_region', 'field' => 'field_region', 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => 'Region', 'entity_type' => 'node', 'entity_field' => 'field_region', 'plugin_id' => 'standard', 'required' => FALSE],
  ],
  'filters' => [
    'status' => ['id' => 'status', 'table' => 'node_field_data', 'field' => 'status', 'plugin_id' => 'boolean', 'value' => '1', 'group' => 1, 'expose' => ['operator' => '']],
    'type' => ['id' => 'type', 'table' => 'node_field_data', 'field' => 'type', 'plugin_id' => 'bundle', 'value' => ['country' => 'country'], 'group' => 1, 'expose' => ['operator' => '']],
    'default_langcode' => ['id' => 'default_langcode', 'table' => 'node_field_data', 'field' => 'default_langcode', 'plugin_id' => 'boolean', 'value' => '1', 'group' => 1, 'expose' => ['operator' => '']],
    'default_langcode_region' => ['id' => 'default_langcode_region', 'table' => 'taxonomy_term_field_data', 'field' => 'default_langcode', 'relationship' => 'field_region', 'plugin_id' => 'boolean', 'value' => '1', 'group' => 1, 'expose' => ['operator' => '']],
  ],
  'sorts' => [
    'title' => ['id' => 'title', 'table' => 'node_field_data', 'field' => 'title', 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => '', 'entity_type' => 'node', 'entity_field' => 'title', 'plugin_id' => 'standard', 'order' => 'ASC', 'exposed' => FALSE, 'expose' => ['label' => '']],
  ],
  'style' => ['type' => 'table', 'options' => [
    'grouping' => [], 'row_class' => '', 'default_row_class' => TRUE, 'override' => TRUE, 'sticky' => FALSE, 'caption' => 'Starter countries, A to Z', 'summary' => '', 'description' => '', 'empty_table' => FALSE,
    'columns' => ['title' => 'title', 'name' => 'name'], 'default' => 'title',
    'info' => ['title' => $col, 'name' => $col],
  ]],
  'row' => ['type' => 'fields'],
  'pager' => ['type' => 'none', 'options' => ['offset' => 0]],
  'exposed_form' => ['type' => 'basic', 'options' => []],
  'access' => ['type' => 'perm', 'options' => ['perm' => 'access content']],
  'cache' => ['type' => 'tag', 'options' => []],
  'query' => ['type' => 'views_query', 'options' => ['query_comment' => '', 'disable_sql_rewrite' => FALSE, 'distinct' => FALSE, 'replica' => FALSE, 'query_tags' => []]],
  'rendering_language' => '***LANGUAGE_language_content***',
  'header' => ['area' => ['id' => 'area', 'table' => 'views', 'field' => 'area', 'plugin_id' => 'text', 'empty' => TRUE,
    'content' => ['value' => '<p>The eight starter countries in this prototype. This is the author\'s selection, not an official target list. Open a country to see its commitments and indicators.</p>', 'format' => 'basic_html']]],
  'empty' => ['area_text_custom' => ['id' => 'area_text_custom', 'table' => 'views', 'field' => 'area_text_custom', 'plugin_id' => 'text_custom', 'empty' => TRUE, 'content' => '<p>No countries are available.</p>']],
];
View::create([
  'id' => 'countries', 'label' => 'Countries', 'module' => 'views', 'description' => 'Public list of countries.', 'tag' => 'camh',
  'base_table' => 'node_field_data', 'base_field' => 'nid', 'langcode' => 'en', 'status' => TRUE,
  'display' => [
    'default' => ['id' => 'default', 'display_title' => 'Default', 'display_plugin' => 'default', 'position' => 0, 'display_options' => $opts],
    'page_1' => ['id' => 'page_1', 'display_title' => 'Page', 'display_plugin' => 'page', 'position' => 1,
      'display_options' => ['path' => 'tracker/countries', 'menu' => ['type' => 'normal', 'title' => 'Countries', 'menu_name' => 'main', 'weight' => 3, 'expanded' => FALSE, 'context' => '']]],
  ],
])->save();
print "Country pages built.\n";
