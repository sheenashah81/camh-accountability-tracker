<?php

/**
 * @file
 * Spanish (AI-assisted, reviewed by a person) for the tracker's own labels and
 * the commitment records:
 *  - module and JavaScript strings from camh_tracker/translations/es.po
 *  - labels in the site name, views, menu items, field labels and footer
 *  - Spanish title and description for each commitment from data/commitments.csv
 *
 * Run from web/:  ddev drush php:script scripts/translate_to_spanish.php
 * Safe to run again.
 */

use Drupal\block_content\Entity\BlockContent;
use Drupal\field\Entity\FieldConfig;
use Drupal\language\Entity\ContentLanguageSettings;

$lm = \Drupal::languageManager();
$set = function (string $name, array $values) use ($lm) {
  $o = $lm->getLanguageConfigOverride('es', $name);
  foreach ($values as $k => $v) {
    $o->set($k, $v);
  }
  $o->save();
};

// 1. Site name.
$set('system.site', ['name' => 'Seguimiento de compromisos en salud mental infantil y adolescente (prototipo)']);

// 2. Commitments list view.
$set('views.view.commitments', [
  'display.default.display_options.title' => 'Compromisos',
  'display.default.display_options.header.result.content' => 'Mostrando @start a @end de @total compromisos',
  'display.default.display_options.header.area.content.value' => '<p>Un compromiso es una promesa pública, ley, política o plan que un gobierno ha adoptado sobre la salud mental de niños y adolescentes. Cada registro enlaza con el documento público del que procede.</p>',
  'display.default.display_options.empty.area_text_custom.content' => '<p><strong>Ningún compromiso coincide con estos filtros.</strong> Pruebe a quitar un filtro o a buscar con menos palabras, o use «Borrar todos los filtros».</p>',
  'display.default.display_options.filters.combine.expose.label' => 'Buscar compromisos',
  'display.default.display_options.filters.field_country_target_id.expose.label' => 'País',
  'display.default.display_options.filters.field_theme_target_id.expose.label' => 'Tema',
  'display.default.display_options.filters.field_region_target_id.expose.label' => 'Región',
  'display.default.display_options.filters.camh_year.expose.label' => 'Año',
  'display.default.display_options.filters.field_status_target_id.expose.label' => 'Estado',
  'display.default.display_options.sorts.changed.expose.label' => 'Actualización más reciente',
  'display.default.display_options.sorts.title_1.expose.label' => 'País de la A a la Z',
  'display.default.display_options.sorts.weight.expose.label' => 'Estado',
  'display.default.display_options.exposed_form.options.submit_button' => 'Aplicar filtros',
  'display.default.display_options.exposed_form.options.reset_button_label' => 'Borrar todos los filtros',
  'display.default.display_options.exposed_form.options.exposed_sorts_label' => 'Ordenar por',
  'display.default.display_options.exposed_form.options.sort_asc_label' => 'Ascendente',
  'display.default.display_options.exposed_form.options.sort_desc_label' => 'Descendente',
  'display.default.display_options.fields.title.label' => 'Compromiso',
  'display.default.display_options.fields.title_1.label' => 'País',
  'display.default.display_options.fields.title_2.label' => 'Institución',
  'display.default.display_options.fields.field_theme.label' => 'Tema',
  'display.default.display_options.fields.field_status.label' => 'Estado',
  'display.default.display_options.fields.changed.label' => 'Última actualización',
  'display.default.display_options.fields.field_is_illustrative.label' => 'Tipo de registro',
  'display.default.display_options.fields.field_is_illustrative.settings.format_custom_true' => 'Ilustrativo',
  'display.default.display_options.style.options.caption' => 'Compromisos publicados',
  'display.default.display_options.pager.options.tags.next' => 'Siguiente',
  'display.default.display_options.pager.options.tags.previous' => 'Anterior',
  'display.default.display_options.pager.options.tags.first' => 'Primera',
  'display.default.display_options.pager.options.tags.last' => 'Última',
  'display.page_1.display_options.menu.title' => 'Compromisos',
]);

// 3. Countries list view.
$set('views.view.countries', [
  'display.default.display_options.title' => 'Países',
  'display.default.display_options.header.area.content.value' => '<p>Los ocho países de partida de este prototipo. Es una selección de la autora, no una lista oficial de países prioritarios. Abra un país para ver sus compromisos e indicadores.</p>',
  'display.default.display_options.empty.area_text_custom.content' => '<p>No hay países disponibles.</p>',
  'display.default.display_options.fields.title.label' => 'País',
  'display.default.display_options.fields.name.label' => 'Región',
  'display.default.display_options.style.options.caption' => 'Países de partida, de la A a la Z',
  'display.page_1.display_options.menu.title' => 'Países',
]);

// Spanish date style: 10 de junio de 2026.
$set('core.date_format.camh_date', ['pattern' => 'j \\d\\e F \\d\\e Y']);
$set('views.view.commitments', ['display.default.display_options.fields.changed.settings.custom_date_format' => 'j \\d\\e F \\d\\e Y']);

// 4. Field labels shown on pages.
$labels = [
  'node.commitment.field_country' => 'País', 'node.commitment.field_institution' => 'Institución',
  'node.commitment.field_theme' => 'Tema', 'node.commitment.field_commitment_type' => 'Tipo de compromiso',
  'node.commitment.field_description' => 'Descripción', 'node.commitment.field_date_made' => 'Fecha de adopción',
  'node.commitment.field_target_date' => 'Fecha objetivo', 'node.commitment.field_status' => 'Estado',
  'node.commitment.field_status_updated_on' => 'Estado actualizado el', 'node.commitment.field_source_name' => 'Nombre de la fuente',
  'node.commitment.field_source_url' => 'URL de la fuente', 'node.commitment.field_licence' => 'Licencia',
  'node.commitment.field_verified_on' => 'Verificado el', 'node.country.field_region' => 'Región',
  'node.country.field_summary' => 'Resumen',
];
foreach ($labels as $key => $label) {
  $set("field.field.$key", ['label' => $label]);
}

// 5. Footer disclaimer (block content translation).
$field = FieldConfig::loadByName('block_content', 'basic', 'body');
if ($field && !$field->isTranslatable()) {
  $field->set('translatable', TRUE)->save();
}
ContentLanguageSettings::loadByEntityTypeBundle('block_content', 'basic')->setDefaultLangcode('en')->setLanguageAlterable(FALSE)->save();
\Drupal::service('content_translation.manager')->setEnabled('block_content', 'basic', TRUE);
foreach (\Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['info' => 'Footer disclaimer']) as $b) {
  if (!$b->hasTranslation('es')) {
    $b->addTranslation('es', ['info' => 'Aviso del pie de página', 'body' => ['format' => 'basic_html', 'value' =>
      '<p><strong>Prototipo de portafolio independiente.</strong> Sin afiliación, aval ni conexión con UNICEF, la OMS, DevInfo, DHIS2 ni ningún gobierno. '
      . 'Los compromisos y los indicadores proceden de documentos y conjuntos de datos públicos citados; cada registro muestra su fuente, su licencia y la fecha en que se comprobó. '
      . 'Los registros marcados como borrador aún no se han revisado. El texto en español se ha elaborado con ayuda de IA y ha sido revisado por una persona.</p>']]);
    $b->save();
  }
}

// 5b. Indicator units in Spanish.
$unit_field = FieldConfig::loadByName('node', 'indicator', 'field_unit');
if ($unit_field && !$unit_field->isTranslatable()) {
  $unit_field->set('translatable', TRUE)->save();
}
$units = ['People' => 'Personas', 'Per 100,000 population' => 'Por 100 000 habitantes'];
foreach (\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'indicator']) as $ind) {
  $en_unit = $ind->getUntranslated()->get('field_unit')->value;
  if (isset($units[$en_unit]) && $ind->hasTranslation('es')) {
    $ind->getTranslation('es')->set('field_unit', $units[$en_unit]);
    $ind->save();
  }
}

// 6. Commitment titles and descriptions in Spanish are created with the records by
// scripts/build_content_model_3.php (from data/commitments.csv), in the same moderation state.
print "Spanish labels saved.\n";
