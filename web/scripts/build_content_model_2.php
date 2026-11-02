<?php

/**
 * @file
 * Build pass 2a of the CAMH tracker content model (data dictionary v0.5):
 * Milestone, Progress update, Indicator, Indicator value and Document types,
 * the Document media type, a user field for the contributor's country, friendly
 * URL patterns and language URL prefixes.
 *
 * Run from the web/ folder:  ddev drush php:script scripts/build_content_model_2.php
 * Safe to run again.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\media\Entity\MediaType;
use Drupal\node\Entity\NodeType;
use Drupal\pathauto\Entity\PathautoPattern;
use Drupal\workflows\Entity\Workflow;

$log = [];

/**
 * Adds a field to a bundle with default form and view display settings.
 */
function camh_add_field(string $entity_type, string $bundle, string $name, string $type, string $label, array $opts = []) {
  $field_name = 'field_' . $name;
  $storage = FieldStorageConfig::loadByName($entity_type, $field_name);
  if (!$storage) {
    $storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => $type,
      'cardinality' => $opts['cardinality'] ?? 1,
      'settings' => $opts['storage_settings'] ?? [],
    ]);
    $storage->save();
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_storage' => $storage,
      'bundle' => $bundle,
      'label' => $label,
      'required' => $opts['required'] ?? FALSE,
      'description' => $opts['description'] ?? '',
      'translatable' => $opts['translatable'] ?? FALSE,
      'settings' => $opts['settings'] ?? [],
      'default_value' => $opts['default_value'] ?? [],
    ])->save();
  }
  $form = EntityFormDisplay::load("$entity_type.$bundle.default") ?: EntityFormDisplay::create([
    'targetEntityType' => $entity_type, 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE,
  ]);
  $form->setComponent($field_name, [
    'type' => $opts['widget'],
    'weight' => $opts['weight'] ?? 10,
    'settings' => $opts['widget_settings'] ?? [],
  ])->save();
  $view = EntityViewDisplay::load("$entity_type.$bundle.default") ?: EntityViewDisplay::create([
    'targetEntityType' => $entity_type, 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE,
  ]);
  $view->setComponent($field_name, [
    'type' => $opts['formatter'],
    'label' => 'above',
    'weight' => $opts['weight'] ?? 10,
    'settings' => $opts['formatter_settings'] ?? [],
  ])->save();
}

/**
 * Shortcut for a reference to taxonomy terms.
 */
function camh_term_ref(string $bundle, string $name, string $label, string $vocab, array $opts = []) {
  camh_add_field('node', $bundle, $name, 'entity_reference', $label, $opts + [
    'storage_settings' => ['target_type' => 'taxonomy_term'],
    'settings' => ['handler' => 'default:taxonomy_term', 'handler_settings' => ['target_bundles' => [$vocab => $vocab]]],
    'widget' => ($opts['cardinality'] ?? 1) === 1 ? 'options_select' : 'options_buttons',
    'formatter' => 'entity_reference_label',
    'formatter_settings' => ['link' => FALSE],
  ]);
}

/**
 * Shortcut for a reference to country or institution content.
 */
function camh_node_ref(string $bundle, string $name, string $label, string $target, array $opts = []) {
  camh_add_field('node', $bundle, $name, 'entity_reference', $label, $opts + [
    'storage_settings' => ['target_type' => 'node'],
    'settings' => ['handler' => 'default:node', 'handler_settings' => ['target_bundles' => [$target => $target]]],
    'widget' => 'options_select',
    'formatter' => 'entity_reference_label',
    'formatter_settings' => ['link' => TRUE],
  ]);
}


$date_opts = fn(int $w, string $desc = '') => [
  'storage_settings' => ['datetime_type' => 'date'], 'widget' => 'datetime_default', 'formatter' => 'datetime_default',
  'formatter_settings' => ['timezone_override' => '', 'format_type' => 'long'], 'weight' => $w, 'description' => $desc,
];
$bool_opts = fn(int $w, string $desc = '') => [
  'required' => TRUE, 'default_value' => [['value' => 0]], 'settings' => ['on_label' => 'Illustrative', 'off_label' => 'Real'],
  'widget' => 'boolean_checkbox', 'formatter' => 'boolean', 'weight' => $w, 'description' => $desc,
];
$str = fn(int $len, int $w, bool $req = TRUE, string $desc = '') => [
  'required' => $req, 'storage_settings' => ['max_length' => $len], 'widget' => 'string_textfield',
  'formatter' => 'string', 'weight' => $w, 'description' => $desc,
];
$url = fn(int $w, string $desc = '') => [
  'settings' => ['link_type' => 16, 'title' => 0], 'widget' => 'link_default', 'formatter' => 'link', 'weight' => $w, 'description' => $desc,
];
$long = fn(int $w, bool $req, bool $tr, string $desc = '') => [
  'required' => $req, 'translatable' => $tr, 'widget' => 'text_textarea', 'formatter' => 'text_default', 'weight' => $w, 'description' => $desc,
];

// Node types.
$types = [
  'milestone' => ['Milestone', 'A dated checkpoint inside a commitment.'],
  'progress_update' => ['Progress update', 'A dated status report on a commitment. Publishing it sets the commitment status.'],
  'indicator' => ['Indicator', 'A measured quantity shown on the dashboard.'],
  'indicator_value' => ['Indicator value', 'One observation: one indicator, one country, one year.'],
  'document' => ['Document', 'A report, policy or dataset documentation held or linked in the repository.'],
];
foreach ($types as $id => [$name, $desc]) {
  if (!NodeType::load($id)) {
    NodeType::create(['type' => $id, 'name' => $name, 'description' => $desc, 'new_revision' => TRUE, 'display_submitted' => FALSE])->save();
    $log[] = "content type $id created";
  }
  $lc = ContentLanguageSettings::loadByEntityTypeBundle('node', $id);
  $lc->setDefaultLangcode('en')->setLanguageAlterable(FALSE)->save();
  \Drupal::service('content_translation.manager')->setEnabled('node', $id, TRUE);
}

// Milestone.
camh_node_ref('milestone', 'commitment', 'Commitment', 'commitment', ['required' => TRUE, 'weight' => 1]);
camh_add_field('node', 'milestone', 'due_date', 'datetime', 'Due date', ['required' => TRUE] + $date_opts(2));
camh_add_field('node', 'milestone', 'completed_on', 'datetime', 'Completed on', $date_opts(3, 'Not before the commitment date and not in the future.'));
camh_term_ref('milestone', 'milestone_status', 'Milestone status', 'status', ['required' => TRUE, 'weight' => 4, 'description' => 'Use Not started, In progress, Completed or Delayed.']);
camh_add_field('node', 'milestone', 'is_illustrative', 'boolean', 'Illustrative record', $bool_opts(5, 'Milestone progress is illustrative unless a public report supports it.'));

// Progress update.
camh_node_ref('progress_update', 'commitment', 'Commitment', 'commitment', ['required' => TRUE, 'weight' => 1]);
camh_add_field('node', 'progress_update', 'update_date', 'datetime', 'Update date', ['required' => TRUE] + $date_opts(2, 'Not in the future.'));
camh_add_field('node', 'progress_update', 'summary', 'text_long', 'Summary', $long(3, TRUE, TRUE, '20 to 800 characters.'));
camh_term_ref('progress_update', 'new_status', 'New status', 'status', ['required' => TRUE, 'weight' => 4, 'description' => 'Publishing this update sets the commitment status.']);
camh_add_field('node', 'progress_update', 'source_url', 'link', 'Supporting evidence URL', $url(5));
camh_add_field('node', 'progress_update', 'is_illustrative', 'boolean', 'Illustrative record', $bool_opts(6));
camh_add_field('node', 'progress_update', 'review_comment', 'text_long', 'Review comment', $long(7, FALSE, TRUE, 'Required when rejected.'));

// Indicator.
camh_add_field('node', 'indicator', 'code', 'string', 'Indicator code', $str(40, 1, TRUE, "The source's own code. Must be unique."));
camh_add_field('node', 'indicator', 'definition', 'text_long', 'Definition', $long(2, TRUE, TRUE, 'Plain-language definition.'));
camh_add_field('node', 'indicator', 'unit', 'string', 'Unit', $str(40, 3, TRUE, 'For example per 100,000 or % of adolescents.'));
camh_term_ref('indicator', 'theme', 'Theme', 'theme', ['required' => TRUE, 'weight' => 4]);
camh_term_ref('indicator', 'direction', 'Direction', 'indicator_direction', ['required' => TRUE, 'weight' => 5]);
camh_add_field('node', 'indicator', 'age_group', 'string', 'Age group', $str(40, 6, TRUE, 'For example 10 to 19 years.'));

// Indicator value.
camh_node_ref('indicator_value', 'indicator', 'Indicator', 'indicator', ['required' => TRUE, 'weight' => 1]);
camh_node_ref('indicator_value', 'country', 'Country', 'country', ['required' => TRUE, 'weight' => 2]);
camh_add_field('node', 'indicator_value', 'year', 'integer', 'Year', [
  'required' => TRUE, 'settings' => ['min' => 1990], 'widget' => 'number', 'formatter' => 'number_integer',
  'formatter_settings' => ['thousand_separator' => ''], 'weight' => 3,
]);
camh_add_field('node', 'indicator_value', 'value', 'decimal', 'Value', [
  'required' => TRUE, 'storage_settings' => ['precision' => 18, 'scale' => 6], 'widget' => 'number',
  'formatter' => 'number_decimal', 'formatter_settings' => ['scale' => 2], 'weight' => 4,
]);
camh_add_field('node', 'indicator_value', 'sex', 'list_string', 'Sex', [
  'storage_settings' => ['allowed_values' => ['Total' => 'Total', 'Female' => 'Female', 'Male' => 'Male']],
  'widget' => 'options_select', 'formatter' => 'list_default', 'weight' => 5,
]);
camh_add_field('node', 'indicator_value', 'notes', 'string_long', 'Notes', [
  'translatable' => TRUE, 'widget' => 'string_textarea', 'formatter' => 'basic_string', 'weight' => 6,
]);
camh_add_field('node', 'indicator_value', 'source_name', 'string', 'Source name', $str(500, 7, TRUE));
camh_add_field('node', 'indicator_value', 'source_url', 'link', 'Source URL', $url(8));
camh_term_ref('indicator_value', 'licence', 'Licence', 'licence', ['required' => TRUE, 'weight' => 9]);
camh_add_field('node', 'indicator_value', 'retrieved_on', 'datetime', 'Retrieved on', ['required' => TRUE] + $date_opts(10, 'Not in the future.'));
camh_add_field('node', 'indicator_value', 'is_illustrative', 'boolean', 'Illustrative record', $bool_opts(11));

// Document media type, then the Document content type.
if (!MediaType::load('document')) {
  $mt = MediaType::create(['id' => 'document', 'label' => 'Document', 'source' => 'file', 'new_revision' => TRUE]);
  $mt->save();
  $sf = $mt->getSource()->createSourceField($mt);
  $sf->getFieldStorageDefinition()->save();
  $sf->save();
  $mt->set('source_configuration', ['source_field' => $sf->getName()])->save();
  $log[] = 'media type document created';
}
camh_term_ref('document', 'document_type', 'Document type', 'document_type', ['required' => TRUE, 'weight' => 1]);
camh_node_ref('document', 'country', 'Country', 'country', ['weight' => 2, 'description' => 'Leave blank for global documents.']);
camh_term_ref('document', 'theme', 'Theme', 'theme', ['cardinality' => -1, 'weight' => 3]);
camh_add_field('node', 'document', 'language', 'list_string', 'Language', [
  'required' => TRUE, 'storage_settings' => ['allowed_values' => ['en' => 'English', 'es' => 'Spanish', 'other' => 'Other']],
  'widget' => 'options_select', 'formatter' => 'list_default', 'weight' => 4,
]);
camh_add_field('node', 'document', 'year', 'integer', 'Year', [
  'required' => TRUE, 'widget' => 'number', 'formatter' => 'number_integer',
  'formatter_settings' => ['thousand_separator' => ''], 'weight' => 5,
]);
camh_add_field('node', 'document', 'file', 'entity_reference', 'File', [
  'storage_settings' => ['target_type' => 'media'],
  'settings' => ['handler' => 'default:media', 'handler_settings' => ['target_bundles' => ['document' => 'document']]],
  'widget' => 'media_library_widget', 'formatter' => 'entity_reference_entity_view', 'weight' => 6,
  'description' => 'PDF, up to 10 MB, and only if the licence permits redistribution. Otherwise use the external link.',
]);
camh_add_field('node', 'document', 'external_url', 'link', 'External URL', $url(7, 'One of File or External URL is required.'));
camh_add_field('node', 'document', 'source_name', 'string', 'Source name', $str(500, 8, TRUE));
camh_term_ref('document', 'licence', 'Licence', 'licence', ['required' => TRUE, 'weight' => 9]);
camh_add_field('node', 'document', 'retrieved_on', 'datetime', 'Retrieved on', $date_opts(10));
camh_add_field('node', 'document', 'is_illustrative', 'boolean', 'Illustrative record', $bool_opts(11));

// Contributor's assigned country (rule V11).
camh_add_field('user', 'user', 'assigned_country', 'entity_reference', 'Assigned country', [
  'storage_settings' => ['target_type' => 'node'],
  'settings' => ['handler' => 'default:node', 'handler_settings' => ['target_bundles' => ['country' => 'country']]],
  'widget' => 'options_select', 'formatter' => 'entity_reference_label', 'weight' => 20,
  'description' => 'Contributors can create and edit records only for this country.',
]);

// Internal verification notes on commitments (working columns from commitments.csv).
camh_add_field('node', 'commitment', 'verification_status', 'string', 'Verification status (internal)', $str(500, 17, FALSE, 'Working note on what has been checked.'));
camh_add_field('node', 'commitment', 'verification_note', 'text_long', 'Verification note (internal)', $long(18, FALSE, FALSE, 'What is still to be checked. Not shown publicly.'));

// Progress updates use the same review workflow as commitments.
$workflow = Workflow::load('commitment_review');
$plugin = $workflow->getTypePlugin();
if (!in_array('progress_update', $plugin->getBundlesForEntityType('node'))) {
  $plugin->addEntityTypeAndBundle('node', 'progress_update');
  $workflow->save();
  $log[] = 'workflow applied to progress_update';
}

// Friendly URL patterns.
foreach (['commitment' => 'commitments', 'country' => 'countries', 'institution' => 'institutions', 'indicator' => 'indicators', 'document' => 'documents'] as $bundle => $path) {
  $pid = "node_$bundle";
  if (!PathautoPattern::load($pid)) {
    PathautoPattern::create([
      'id' => $pid, 'label' => ucfirst($bundle), 'type' => 'canonical_entities:node',
      'pattern' => "/$path/[node:title]", 'weight' => 0,
      'selection_criteria' => [[
        'id' => 'entity_bundle:node', 'negate' => FALSE, 'context_mapping' => ['node' => 'node'], 'bundles' => [$bundle => $bundle],
      ]],
    ])->save();
    $log[] = "url pattern $pid created";
  }
}

// Language URL prefixes: /en and /es.
$neg = \Drupal::configFactory()->getEditable('language.negotiation');
$neg->set('url.source', 'path_prefix')->set('url.prefixes', ['en' => 'en', 'es' => 'es'])->save();
$types_cfg = \Drupal::configFactory()->getEditable('language.types');
$types_cfg->set('negotiation.language_interface.enabled', ['language-url' => -8, 'language-selected' => 12])->save();
$log[] = 'language URL prefixes set';

print implode("\n", $log) . "\nDone.\n";
