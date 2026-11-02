<?php

/**
 * @file
 * Builds the public Commitments list (Drupal View "commitments") from the
 * wireframe spec docs/wireframe-specs/commitments-list.md.
 *
 * Page: /en/tracker/commitments. Published commitments only. Search, country,
 * theme and status filters; sort by recently updated, country or status; table
 * of 20 per page; empty-state message; clear-all-filters button.
 *
 * Run from web/:  ddev drush php:script scripts/build_commitments_list.php
 * Re-running replaces the view.
 */

use Drupal\views\Entity\View;

if ($existing = View::load('commitments')) {
  $existing->delete();
}

$ref_filter = function (string $field, string $table, string $label, string $identifier, string $handler, array $bundles, string $sort_field) {
  return [
    'id' => $field . '_target_id', 'table' => $table, 'field' => $field . '_target_id',
    'plugin_id' => 'entity_reference', 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => '',
    'operator' => 'or', 'value' => [], 'group' => 1, 'exposed' => TRUE,
    'expose' => [
      'operator_id' => $field . '_target_id_op', 'label' => $label, 'description' => '', 'use_operator' => FALSE,
      'operator' => $field . '_target_id_op', 'operator_limit_selection' => FALSE, 'operator_list' => [],
      'identifier' => $identifier, 'required' => FALSE, 'remember' => FALSE, 'multiple' => FALSE,
      'remember_roles' => ['authenticated' => 'authenticated'], 'reduce' => FALSE,
    ],
    'is_grouped' => FALSE, 'group_info' => [],
    'sub_handler' => 'default:' . $handler,
    'sub_handler_settings' => ['target_bundles' => array_combine($bundles, $bundles), 'sort' => ['field' => $sort_field, 'direction' => 'ASC'], 'auto_create' => FALSE],
    'widget' => 'select', 'entity_type' => 'node', 'entity_field' => $field,
  ];
};

$term_filter = function (string $field, string $label, string $identifier, string $vocabulary) {
  return [
    'id' => $field . '_target_id', 'table' => 'node__' . $field, 'field' => $field . '_target_id',
    'plugin_id' => 'taxonomy_index_tid', 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => '',
    'operator' => 'or', 'value' => [], 'group' => 1, 'exposed' => TRUE,
    'expose' => [
      'operator_id' => $field . '_target_id_op', 'label' => $label, 'description' => '', 'use_operator' => FALSE,
      'operator' => $field . '_target_id_op', 'operator_limit_selection' => FALSE, 'operator_list' => [],
      'identifier' => $identifier, 'required' => FALSE, 'remember' => FALSE, 'multiple' => FALSE,
      'remember_roles' => ['authenticated' => 'authenticated'], 'reduce' => FALSE,
    ],
    'is_grouped' => FALSE, 'group_info' => [],
    'type' => 'select', 'limit' => TRUE, 'vid' => $vocabulary, 'hierarchy' => FALSE, 'error_message' => TRUE,
    'entity_type' => 'node', 'entity_field' => $field,
  ];
};

$field = function (string $id, string $table, string $name, string $label, string $type, array $settings = [], string $rel = 'none', array $extra = []) {
  return $extra + [
    'id' => $id, 'table' => $table, 'field' => $name, 'relationship' => $rel, 'group_type' => 'group', 'admin_label' => '',
    'label' => $label, 'exclude' => FALSE, 'alter' => ['alter_text' => FALSE, 'make_link' => FALSE, 'absolute' => FALSE, 'word_boundary' => FALSE, 'ellipsis' => FALSE, 'strip_tags' => FALSE, 'trim' => FALSE, 'html' => FALSE],
    'element_type' => '', 'element_class' => '', 'element_label_type' => '', 'element_label_class' => '', 'element_label_colon' => FALSE,
    'element_wrapper_type' => '', 'element_wrapper_class' => '', 'element_default_classes' => TRUE,
    'empty' => '', 'hide_empty' => FALSE, 'empty_zero' => FALSE, 'hide_alter_empty' => TRUE,
    'click_sort_column' => 'value', 'type' => $type, 'settings' => $settings, 'group_column' => 'value', 'group_columns' => [],
    'group_rows' => TRUE, 'delta_limit' => 0, 'delta_offset' => 0, 'delta_reversed' => FALSE, 'delta_first_last' => FALSE,
    'multi_type' => 'separator', 'separator' => ', ', 'field_api_classes' => FALSE, 'plugin_id' => 'field',
  ];
};

$display_options = [
  'title' => 'Commitments',
  'fields' => [
    'title' => $field('title', 'node_field_data', 'title', 'Commitment', 'string', ['link_to_entity' => TRUE], 'none', ['entity_type' => 'node', 'entity_field' => 'title']),
    'title_1' => $field('title_1', 'node_field_data', 'title', 'Country', 'string', ['link_to_entity' => FALSE], 'field_country', ['entity_type' => 'node', 'entity_field' => 'title']),
    'title_2' => $field('title_2', 'node_field_data', 'title', 'Institution', 'string', ['link_to_entity' => FALSE], 'field_institution', ['entity_type' => 'node', 'entity_field' => 'title']),
    'field_theme' => $field('field_theme', 'node__field_theme', 'field_theme', 'Theme', 'entity_reference_label', ['link' => FALSE], 'none', ['click_sort_column' => 'target_id']),
    'field_status' => $field('field_status', 'node__field_status', 'field_status', 'Status', 'camh_status_badge', [], 'none', ['click_sort_column' => 'target_id']),
    'changed' => $field('changed', 'node_field_data', 'changed', 'Last updated', 'timestamp', ['date_format' => 'custom', 'custom_date_format' => 'j F Y', 'timezone' => '', 'tooltip' => ['date_format' => '', 'custom_date_format' => ''], 'time_diff' => ['enabled' => FALSE, 'future_format' => '@interval hence', 'past_format' => '@interval ago', 'granularity' => 2, 'refresh' => 60]], 'none', ['entity_type' => 'node', 'entity_field' => 'changed', 'click_sort_column' => 'value']),
    'field_is_illustrative' => $field('field_is_illustrative', 'node__field_is_illustrative', 'field_is_illustrative', 'Record type', 'boolean', ['format' => 'custom', 'format_custom_true' => 'Illustrative', 'format_custom_false' => ''], 'none', ['click_sort_column' => 'value']),
    'field_description' => $field('field_description', 'node__field_description', 'field_description', 'Description', 'text_default', [], 'none', ['exclude' => TRUE]),
  ],
  'relationships' => [
    'field_country' => ['id' => 'field_country', 'table' => 'node__field_country', 'field' => 'field_country', 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => 'Country', 'entity_type' => 'node', 'entity_field' => 'field_country', 'plugin_id' => 'standard', 'required' => FALSE],
    'field_institution' => ['id' => 'field_institution', 'table' => 'node__field_institution', 'field' => 'field_institution', 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => 'Institution', 'entity_type' => 'node', 'entity_field' => 'field_institution', 'plugin_id' => 'standard', 'required' => FALSE],
    'field_status' => ['id' => 'field_status', 'table' => 'node__field_status', 'field' => 'field_status', 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => 'Status term', 'entity_type' => 'node', 'entity_field' => 'field_status', 'plugin_id' => 'standard', 'required' => FALSE],
  ],
  'filters' => [
    'status' => ['id' => 'status', 'table' => 'node_field_data', 'field' => 'status', 'plugin_id' => 'boolean', 'value' => '1', 'group' => 1, 'expose' => ['operator' => '']],
    'type' => ['id' => 'type', 'table' => 'node_field_data', 'field' => 'type', 'plugin_id' => 'bundle', 'value' => ['commitment' => 'commitment'], 'group' => 1, 'expose' => ['operator' => '']],
    // One row per commitment: original-language rows only; titles and labels are shown in the visitor's language when a translation exists.
    'default_langcode' => ['id' => 'default_langcode', 'table' => 'node_field_data', 'field' => 'default_langcode', 'plugin_id' => 'boolean', 'value' => '1', 'group' => 1, 'expose' => ['operator' => '']],
    'default_langcode_country' => ['id' => 'default_langcode_country', 'table' => 'node_field_data', 'field' => 'default_langcode', 'relationship' => 'field_country', 'plugin_id' => 'boolean', 'value' => '1', 'group' => 1, 'expose' => ['operator' => '']],
    'default_langcode_institution' => ['id' => 'default_langcode_institution', 'table' => 'node_field_data', 'field' => 'default_langcode', 'relationship' => 'field_institution', 'plugin_id' => 'boolean', 'value' => '1', 'group' => 1, 'expose' => ['operator' => '']],
    'combine' => [
      'id' => 'combine', 'table' => 'views', 'field' => 'combine', 'plugin_id' => 'combine', 'relationship' => 'none', 'group_type' => 'group',
      'admin_label' => '', 'operator' => 'contains', 'value' => '', 'group' => 1, 'exposed' => TRUE,
      'expose' => ['operator_id' => 'combine_op', 'label' => 'Search commitments', 'description' => '', 'use_operator' => FALSE, 'operator' => 'combine_op', 'identifier' => 'search', 'required' => FALSE, 'remember' => FALSE, 'multiple' => FALSE, 'remember_roles' => ['authenticated' => 'authenticated']],
      'is_grouped' => FALSE, 'group_info' => [], 'fields' => ['title' => 'title', 'field_description' => 'field_description'],
    ],
    'field_country_target_id' => $ref_filter('field_country', 'node__field_country', 'Country', 'country', 'node', ['country'], 'title'),
    'field_region_target_id' => ['id' => 'field_region_target_id', 'table' => 'node__field_region', 'field' => 'field_region_target_id', 'relationship' => 'field_country',
      'plugin_id' => 'taxonomy_index_tid', 'group_type' => 'group', 'admin_label' => '', 'operator' => 'or', 'value' => [], 'group' => 1, 'exposed' => TRUE,
      'expose' => ['operator_id' => 'field_region_target_id_op', 'label' => 'Region', 'description' => '', 'use_operator' => FALSE, 'operator' => 'field_region_target_id_op', 'operator_limit_selection' => FALSE, 'operator_list' => [], 'identifier' => 'region', 'required' => FALSE, 'remember' => FALSE, 'multiple' => FALSE, 'remember_roles' => ['authenticated' => 'authenticated'], 'reduce' => FALSE],
      'is_grouped' => FALSE, 'group_info' => [], 'type' => 'select', 'limit' => TRUE, 'vid' => 'region', 'hierarchy' => FALSE, 'error_message' => TRUE, 'entity_type' => 'node', 'entity_field' => 'field_region'],
    'field_theme_target_id' => $term_filter('field_theme', 'Theme', 'theme', 'theme'),
    'field_status_target_id' => $term_filter('field_status', 'Status', 'status', 'status'),
    'camh_year' => ['id' => 'camh_year', 'table' => 'node__field_date_made', 'field' => 'camh_year', 'relationship' => 'none', 'plugin_id' => 'camh_year', 'group_type' => 'group', 'admin_label' => '',
      'operator' => 'in', 'value' => [], 'group' => 1, 'exposed' => TRUE,
      'expose' => ['operator_id' => 'camh_year_op', 'label' => 'Year', 'description' => '', 'use_operator' => FALSE, 'operator' => 'camh_year_op', 'operator_limit_selection' => FALSE, 'operator_list' => [], 'identifier' => 'year', 'required' => FALSE, 'remember' => FALSE, 'multiple' => FALSE, 'remember_roles' => ['authenticated' => 'authenticated'], 'reduce' => FALSE],
      'is_grouped' => FALSE, 'group_info' => []],
  ],
  'sorts' => [
    'changed' => ['id' => 'changed', 'table' => 'node_field_data', 'field' => 'changed', 'relationship' => 'none', 'group_type' => 'group', 'admin_label' => '', 'entity_type' => 'node', 'entity_field' => 'changed', 'plugin_id' => 'date', 'order' => 'DESC', 'exposed' => TRUE, 'expose' => ['label' => 'Most recently updated', 'field_identifier' => 'changed'], 'granularity' => 'second'],
    'title_1' => ['id' => 'title_1', 'table' => 'node_field_data', 'field' => 'title', 'relationship' => 'field_country', 'group_type' => 'group', 'admin_label' => '', 'entity_type' => 'node', 'entity_field' => 'title', 'plugin_id' => 'standard', 'order' => 'ASC', 'exposed' => TRUE, 'expose' => ['label' => 'Country A to Z', 'field_identifier' => 'title_1']],
    'weight' => ['id' => 'weight', 'table' => 'taxonomy_term_field_data', 'field' => 'weight', 'relationship' => 'field_status', 'group_type' => 'group', 'admin_label' => '', 'entity_type' => 'taxonomy_term', 'entity_field' => 'weight', 'plugin_id' => 'standard', 'order' => 'ASC', 'exposed' => TRUE, 'expose' => ['label' => 'Status', 'field_identifier' => 'weight']],
  ],
  'style' => [
    'type' => 'table',
    'options' => [
      'grouping' => [], 'row_class' => '', 'default_row_class' => TRUE, 'override' => TRUE, 'sticky' => FALSE, 'caption' => 'Published commitments', 'summary' => '', 'description' => '', 'empty_table' => FALSE,
      'columns' => ['title' => 'title', 'title_1' => 'title_1', 'title_2' => 'title_2', 'field_theme' => 'field_theme', 'field_status' => 'field_status', 'changed' => 'changed', 'field_is_illustrative' => 'field_is_illustrative', 'field_description' => 'field_description'],
      'default' => '-1', 'info' => [
        'title' => ['sortable' => TRUE, 'default_sort_order' => 'asc', 'align' => '', 'separator' => '', 'empty_column' => FALSE, 'responsive' => ''],
        'title_1' => ['sortable' => TRUE, 'default_sort_order' => 'asc', 'align' => '', 'separator' => '', 'empty_column' => FALSE, 'responsive' => ''],
        'title_2' => ['sortable' => FALSE, 'default_sort_order' => 'asc', 'align' => '', 'separator' => '', 'empty_column' => FALSE, 'responsive' => ''],
        'field_theme' => ['sortable' => FALSE, 'default_sort_order' => 'asc', 'align' => '', 'separator' => '', 'empty_column' => FALSE, 'responsive' => ''],
        'field_status' => ['sortable' => FALSE, 'default_sort_order' => 'asc', 'align' => '', 'separator' => '', 'empty_column' => FALSE, 'responsive' => ''],
        'changed' => ['sortable' => TRUE, 'default_sort_order' => 'desc', 'align' => '', 'separator' => '', 'empty_column' => FALSE, 'responsive' => ''],
        'field_is_illustrative' => ['sortable' => FALSE, 'default_sort_order' => 'asc', 'align' => '', 'separator' => '', 'empty_column' => TRUE, 'responsive' => ''],
        'field_description' => ['sortable' => FALSE, 'default_sort_order' => 'asc', 'align' => '', 'separator' => '', 'empty_column' => FALSE, 'responsive' => ''],
      ],
    ],
  ],
  'row' => ['type' => 'fields'],
  'pager' => ['type' => 'full', 'options' => ['offset' => 0, 'items_per_page' => 20, 'total_pages' => NULL, 'id' => 0, 'tags' => ['next' => 'Next', 'previous' => 'Previous', 'first' => 'First', 'last' => 'Last'], 'expose' => ['items_per_page' => FALSE], 'quantity' => 5]],
  'exposed_form' => ['type' => 'basic', 'options' => [
    'submit_button' => 'Apply filters', 'reset_button' => TRUE, 'reset_button_label' => 'Clear all filters',
    'exposed_sorts_label' => 'Sort by', 'expose_sort_order' => FALSE, 'sort_asc_label' => 'Ascending', 'sort_desc_label' => 'Descending',
  ]],
  'access' => ['type' => 'perm', 'options' => ['perm' => 'access content']],
  'cache' => ['type' => 'tag', 'options' => []],
  'query' => ['type' => 'views_query', 'options' => ['query_comment' => '', 'disable_sql_rewrite' => FALSE, 'distinct' => TRUE, 'replica' => FALSE, 'query_tags' => []]],
  'use_ajax' => FALSE,
  'rendering_language' => '***LANGUAGE_language_content***',
  'header' => [
    'result' => ['id' => 'result', 'table' => 'views', 'field' => 'result', 'plugin_id' => 'result', 'empty' => FALSE, 'content' => 'Showing @start to @end of @total commitments'],
    'area' => ['id' => 'area', 'table' => 'views', 'field' => 'area', 'plugin_id' => 'text', 'empty' => TRUE, 'weight' => -1,
      'content' => ['value' => '<p>A commitment is a public pledge, law, policy or plan that a government has made about child and adolescent mental health. Each record links to the public document it comes from.</p>', 'format' => 'basic_html']],
  ],
  'empty' => [
    'area_text_custom' => ['id' => 'area_text_custom', 'table' => 'views', 'field' => 'area_text_custom', 'plugin_id' => 'text_custom', 'empty' => TRUE,
      'content' => '<p><strong>No commitments match these filters.</strong> Try removing a filter or searching with fewer words, or use "Clear all filters".</p>'],
  ],
  'display_extenders' => [],
];

$view = View::create([
  'id' => 'commitments', 'label' => 'Commitments', 'module' => 'views', 'description' => 'Public list of published commitments.',
  'tag' => 'camh', 'base_table' => 'node_field_data', 'base_field' => 'nid', 'langcode' => 'en', 'status' => TRUE,
  'display' => [
    'default' => ['id' => 'default', 'display_title' => 'Default', 'display_plugin' => 'default', 'position' => 0, 'display_options' => $display_options],
    'page_1' => ['id' => 'page_1', 'display_title' => 'Page', 'display_plugin' => 'page', 'position' => 1,
      'display_options' => ['path' => 'tracker/commitments', 'menu' => ['type' => 'normal', 'title' => 'Commitments', 'menu_name' => 'main', 'weight' => 1, 'expanded' => FALSE, 'context' => '']]],
  ],
]);
$view->save();
print "View saved.\n";
