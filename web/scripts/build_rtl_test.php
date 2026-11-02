<?php

/**
 * @file
 * Adds Arabic as a right-to-left layout test language (no Arabic content).
 * Reachable at /ar; hidden from the language switcher by the camh_tracker module.
 *
 * Run from web/:  ddev drush php:script scripts/build_rtl_test.php
 */

use Drupal\language\Entity\ConfigurableLanguage;

if (!ConfigurableLanguage::load('ar')) {
  ConfigurableLanguage::createFromLangcode('ar')->save();
}
$neg = \Drupal::configFactory()->getEditable('language.negotiation');
$neg->set('url.prefixes', ['en' => 'en', 'es' => 'es', 'ar' => 'ar'])->save();
print "Arabic layout-test language ready (direction: " . ConfigurableLanguage::load('ar')->getDirection() . ").\n";
