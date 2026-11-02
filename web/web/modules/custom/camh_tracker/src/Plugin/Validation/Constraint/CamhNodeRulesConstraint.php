<?php

namespace Drupal\camh_tracker\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Data dictionary validation rules for CAMH Tracker content.
 */
#[Constraint(
  id: 'CamhNodeRules',
  label: new TranslatableMarkup('CAMH Tracker data rules', [], ['context' => 'Validation']),
)]
class CamhNodeRulesConstraint extends SymfonyConstraint {

}
