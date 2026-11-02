<?php

/**
 * @file
 * Build pass 1 of the CAMH tracker content model (data dictionary v0.4).
 *
 * Creates: Spanish language, translation and moderation modules, the fixed
 * lists (vocabularies, filled from data/vocabularies.csv), the Country,
 * Institution and Commitment content types, and the Commitment review workflow.
 *
 * Run from the web/ folder:  ddev drush php:script scripts/build_content_model_1.php
 * Safe to run again: anything that already exists is skipped.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\workflows\Entity\Workflow;

$log = [];

// 1. Modules and languages.
\Drupal::service('module_installer')->install([
  'language', 'content_translation', 'config_translation',
  'content_moderation', 'workflows', 'datetime', 'link', 'options', 'taxonomy',
]);
if (!ConfigurableLanguage::load('es')) {
  ConfigurableLanguage::createFromLangcode('es')->save();
  $log[] = 'language es added';
}

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

// 2. Fixed lists, filled from data/vocabularies.csv. Workflow state is not a
// list: it is handled by the moderation workflow below.
$vocab_names = [
  'theme' => 'Theme', 'status' => 'Status', 'commitment_type' => 'Commitment type',
  'region' => 'Region', 'licence' => 'Licence', 'document_type' => 'Document type',
  'institution_type' => 'Institution type', 'indicator_direction' => 'Indicator direction',
];
$csv = array_map('str_getcsv', file('/var/www/data/vocabularies.csv', FILE_IGNORE_NEW_LINES));
$header = array_shift($csv);
$rows = array_map(fn($r) => array_combine($header, $r), $csv);
foreach ($vocab_names as $vid => $label) {
  if (!Vocabulary::load($vid)) {
    Vocabulary::create(['vid' => $vid, 'name' => $label])->save();
    $log[] = "vocabulary $vid created";
  }
  camh_add_field('taxonomy_term', $vid, 'term_code', 'string', 'Term code', [
    'required' => TRUE, 'widget' => 'string_textfield', 'formatter' => 'string', 'weight' => 5,
  ]);
  $lc = ContentLanguageSettings::loadByEntityTypeBundle('taxonomy_term', $vid);
  $lc->setDefaultLangcode('en')->setLanguageAlterable(FALSE)->save();
  \Drupal::service('content_translation.manager')->setEnabled('taxonomy_term', $vid, TRUE);
}
$created_terms = 0;
foreach ($rows as $row) {
  $vid = $row['vocabulary'];
  if (!isset($vocab_names[$vid])) {
    continue;
  }
  $exists = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid, 'field_term_code' => $row['term_code']]);
  if ($exists) {
    continue;
  }
  $term = Term::create([
    'vid' => $vid, 'name' => $row['label_en'], 'field_term_code' => $row['term_code'],
    'weight' => (int) $row['sort_order'], 'langcode' => 'en',
  ]);
  $term->save();
  $term->addTranslation('es', ['name' => $row['label_es']])->save();
  $created_terms++;
}
$log[] = "$created_terms terms created";

// 3. Content types.
$types = [
  'country' => ['Country', 'One of the eight starter countries.'],
  'institution' => ['Institution', 'A ministry, agency or other body responsible for a commitment.'],
  'commitment' => ['Commitment', 'A public commitment supported by a cited public document.'],
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

// Country.
camh_add_field('node', 'country', 'iso3', 'string', 'ISO 3166-1 alpha-3 code', [
  'required' => TRUE, 'description' => 'Three uppercase letters, for example KEN. Must be unique.',
  'storage_settings' => ['max_length' => 3], 'widget' => 'string_textfield', 'formatter' => 'string', 'weight' => 1,
]);
camh_term_ref('country', 'region', 'Region', 'region', ['required' => TRUE, 'weight' => 2]);
camh_add_field('node', 'country', 'summary', 'text_long', 'Summary', [
  'translatable' => TRUE, 'description' => 'Plain-language profile introduction.',
  'widget' => 'text_textarea', 'formatter' => 'text_default', 'weight' => 3,
]);
camh_add_field('node', 'country', 'is_starter', 'boolean', 'Starter country', [
  'required' => TRUE, 'default_value' => [['value' => 1]], 'settings' => ['on_label' => 'Yes', 'off_label' => 'No'],
  'widget' => 'boolean_checkbox', 'formatter' => 'boolean', 'weight' => 4,
]);

// Institution.
camh_add_field('node', 'institution', 'code', 'string', 'Institution code', [
  'required' => TRUE, 'description' => 'Format ISO3-ABBREVIATION, for example KEN-MOH. Must be unique.',
  'storage_settings' => ['max_length' => 20], 'widget' => 'string_textfield', 'formatter' => 'string', 'weight' => 1,
]);
camh_node_ref('institution', 'country', 'Country', 'country', ['required' => TRUE, 'weight' => 2]);
camh_term_ref('institution', 'institution_type', 'Institution type', 'institution_type', ['required' => TRUE, 'weight' => 3]);
camh_add_field('node', 'institution', 'website', 'link', 'Website', [
  'settings' => ['link_type' => 16, 'title' => 0], 'widget' => 'link_default', 'formatter' => 'link', 'weight' => 4,
]);

// Commitment.
camh_node_ref('commitment', 'country', 'Country', 'country', ['required' => TRUE, 'weight' => 1]);
camh_node_ref('commitment', 'institution', 'Institution', 'institution', [
  'required' => TRUE, 'weight' => 2, 'description' => 'The institution must belong to the same country as the commitment.',
]);
camh_term_ref('commitment', 'theme', 'Theme', 'theme', ['required' => TRUE, 'cardinality' => -1, 'weight' => 3]);
camh_term_ref('commitment', 'commitment_type', 'Commitment type', 'commitment_type', ['required' => TRUE, 'weight' => 4]);
camh_add_field('node', 'commitment', 'description', 'text_long', 'Description', [
  'required' => TRUE, 'translatable' => TRUE, 'description' => 'Plain language, 50 to 1,200 characters.',
  'widget' => 'text_textarea', 'formatter' => 'text_default', 'weight' => 5,
]);
camh_add_field('node', 'commitment', 'date_made', 'datetime', 'Date made or adopted', [
  'storage_settings' => ['datetime_type' => 'date'], 'widget' => 'datetime_default', 'formatter' => 'datetime_default',
  'formatter_settings' => ['timezone_override' => '', 'format_type' => 'long'], 'weight' => 6,
  'description' => 'Required before the commitment goes to review.',
]);
camh_add_field('node', 'commitment', 'target_date', 'datetime', 'Target date', [
  'storage_settings' => ['datetime_type' => 'date'], 'widget' => 'datetime_default', 'formatter' => 'datetime_default',
  'formatter_settings' => ['timezone_override' => '', 'format_type' => 'long'], 'weight' => 7,
]);
camh_term_ref('commitment', 'status', 'Status', 'status', ['weight' => 8, 'description' => 'Leave blank while a draft has not been assessed.']);
camh_add_field('node', 'commitment', 'status_updated_on', 'datetime', 'Status last updated on', [
  'storage_settings' => ['datetime_type' => 'date'], 'widget' => 'datetime_default', 'formatter' => 'datetime_default',
  'formatter_settings' => ['timezone_override' => '', 'format_type' => 'long'], 'weight' => 9,
]);
camh_add_field('node', 'commitment', 'source_name', 'string', 'Source name', [
  'required' => TRUE, 'description' => 'Publisher and title of the public document.',
  'storage_settings' => ['max_length' => 500], 'widget' => 'string_textfield', 'formatter' => 'string', 'weight' => 10,
]);
camh_add_field('node', 'commitment', 'source_url', 'link', 'Source URL', [
  'settings' => ['link_type' => 16, 'title' => 0], 'widget' => 'link_default', 'formatter' => 'link', 'weight' => 11,
  'description' => 'Must resolve to the public source. Required unless the record is illustrative.',
]);
camh_term_ref('commitment', 'licence', 'Licence', 'licence', ['required' => TRUE, 'weight' => 12, 'description' => 'A record marked Not yet checked cannot be published.']);
camh_add_field('node', 'commitment', 'verified_on', 'datetime', 'Verified on', [
  'storage_settings' => ['datetime_type' => 'date'], 'widget' => 'datetime_default', 'formatter' => 'datetime_default',
  'formatter_settings' => ['timezone_override' => '', 'format_type' => 'long'], 'weight' => 13,
  'description' => 'Date a person confirmed the source supports this record.',
]);
camh_add_field('node', 'commitment', 'is_illustrative', 'boolean', 'Illustrative record', [
  'required' => TRUE, 'default_value' => [['value' => 0]], 'settings' => ['on_label' => 'Illustrative', 'off_label' => 'Real'],
  'widget' => 'boolean_checkbox', 'formatter' => 'boolean', 'weight' => 14,
  'description' => 'Tick only for records added to demonstrate workflows. They show an Illustrative tag on screen.',
]);
camh_add_field('node', 'commitment', 'review_comment', 'text_long', 'Review comment', [
  'translatable' => TRUE, 'widget' => 'text_textarea', 'formatter' => 'text_default', 'weight' => 15,
  'description' => 'Required when a record is rejected.',
]);
camh_add_field('node', 'commitment', 'es_review_status', 'list_string', 'Spanish text review status', [
  'storage_settings' => ['allowed_values' => ['pending' => 'Pending human review', 'reviewed' => 'Reviewed by a person']],
  'default_value' => [['value' => 'pending']], 'widget' => 'options_select', 'formatter' => 'list_default', 'weight' => 16,
  'description' => 'Spanish text is shown as pending human review until set to Reviewed.',
]);

// 4. Commitment review workflow (Draft, In review, Published, Rejected).
if (!Workflow::load('commitment_review')) {
  $workflow = Workflow::create(['id' => 'commitment_review', 'label' => 'Commitment review', 'type' => 'content_moderation']);
  $plugin = $workflow->getTypePlugin();
  // Content moderation workflows start with Draft and Published states.
  $plugin->addState('in_review', 'In review');
  $plugin->addState('rejected', 'Rejected');
  $config = $plugin->getConfiguration();
  $config['states']['draft'] += ['published' => FALSE, 'default_revision' => FALSE];
  $config['states']['in_review'] += ['published' => FALSE, 'default_revision' => FALSE];
  $config['states']['published'] += ['published' => TRUE, 'default_revision' => TRUE];
  $config['states']['rejected'] += ['published' => FALSE, 'default_revision' => FALSE];
  $plugin->setConfiguration($config);
  // Defaults: create_new_draft (Draft or Published to Draft) is kept. The
  // default publish transition from Draft is removed so that nothing can be
  // published without passing through In review.
  $plugin->deleteTransition('publish');
  $plugin->addTransition('submit_for_review', 'Submit for review', ['draft'], 'in_review');
  $plugin->addTransition('reject', 'Reject with comment', ['in_review'], 'rejected');
  $plugin->addTransition('approve_and_publish', 'Approve and publish', ['in_review'], 'published');
  $plugin->addTransition('back_to_draft', 'Return to draft', ['rejected'], 'draft');
  $plugin->addEntityTypeAndBundle('node', 'commitment');
  $workflow->save();
  $log[] = 'workflow commitment_review created';
}

print implode("\n", $log) . "\nDone.\n";
