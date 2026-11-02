<?php

namespace Drupal\camh_tracker\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the data dictionary rules (section 4) for each content type.
 *
 * Rule numbers (V1 to V12, P1) refer to data/data-dictionary.md.
 */
class CamhNodeRulesConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AccountProxyInterface $currentUser,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('entity_type.manager'), $container->get('current_user'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $entity, Constraint $constraint): void {
    if (!$entity instanceof NodeInterface) {
      return;
    }
    match ($entity->bundle()) {
      'country' => $this->country($entity),
      'institution' => $this->institution($entity),
      'commitment' => $this->commitment($entity),
      'milestone' => $this->milestone($entity),
      'progress_update' => $this->progressUpdate($entity),
      'indicator' => $this->indicator($entity),
      'indicator_value' => $this->indicatorValue($entity),
      'document' => $this->document($entity),
      default => NULL,
    };
  }

  protected function fail(string $message): void {
    $this->context->addViolation($message);
  }

  protected function val(NodeInterface $n, string $field): mixed {
    return $n->hasField($field) ? $n->get($field)->value : NULL;
  }

  protected function ref(NodeInterface $n, string $field): mixed {
    return $n->hasField($field) ? $n->get($field)->target_id : NULL;
  }

  protected function termCode(NodeInterface $n, string $field): ?string {
    $term = $n->hasField($field) ? $n->get($field)->entity : NULL;
    return $term ? $term->get('field_term_code')->value : NULL;
  }

  protected function moderationState(NodeInterface $n): string {
    return $n->hasField('moderation_state') ? (string) $n->get('moderation_state')->value : '';
  }

  /**
   * Whether another node of the bundle has the same value(s) (rule V5).
   */
  protected function exists(NodeInterface $n, array $conditions): bool {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()->accessCheck(FALSE)->condition('type', $n->bundle());
    foreach ($conditions as $field => $value) {
      $query->condition($field, $value);
    }
    if (!$n->isNew()) {
      $query->condition('nid', $n->id(), '<>');
    }
    return (bool) $query->range(0, 1)->execute();
  }

  protected function notFuture(NodeInterface $n, string $field, string $label): void {
    $v = $this->val($n, $field);
    if ($v && $v > date('Y-m-d')) {
      $this->fail("$label cannot be in the future (rule V3).");
    }
  }

  protected function country(NodeInterface $n): void {
    $iso = (string) $this->val($n, 'field_iso3');
    if (!preg_match('/^[A-Z]{3}$/', $iso)) {
      $this->fail('The country code must be three uppercase letters, for example KEN.');
    }
    elseif ($this->exists($n, ['field_iso3' => $iso])) {
      $this->fail("A country with code $iso already exists (rule V5).");
    }
  }

  protected function institution(NodeInterface $n): void {
    $code = (string) $this->val($n, 'field_code');
    if (!preg_match('/^[A-Z]{3}-[A-Z0-9]+$/', $code)) {
      $this->fail('The institution code must look like KEN-MOH (country code, hyphen, abbreviation).');
    }
    elseif ($this->exists($n, ['field_code' => $code])) {
      $this->fail("An institution with code $code already exists (rule V5).");
    }
    $country = $n->get('field_country')->entity;
    if ($country && $code && !str_starts_with($code, (string) $country->get('field_iso3')->value . '-')) {
      $this->fail('The institution code must start with the code of its country.');
    }
  }

  protected function commitment(NodeInterface $n): void {
    $state = $this->moderationState($n);
    $country_id = $this->ref($n, 'field_country');
    // V5: title unique within a country.
    if ($country_id && $n->label() && $this->exists($n, ['title' => $n->label(), 'field_country' => $country_id])) {
      $this->fail('Another commitment for this country already has this title (rule V5).');
    }
    // V6: institution country equals commitment country.
    $inst = $n->get('field_institution')->entity;
    if ($inst && $country_id && $inst->get('field_country')->target_id != $country_id) {
      $this->fail("The institution's country must be the same as the commitment's country (rule V6).");
    }
    // Description length (data dictionary: 50 to 1,200 characters).
    $desc = trim(strip_tags((string) $this->val($n, 'field_description')));
    if ($desc !== '' && (mb_strlen($desc) < 50 || mb_strlen($desc) > 1200)) {
      $this->fail('The description must be between 50 and 1,200 characters.');
    }
    // V3: dates.
    $made = $this->val($n, 'field_date_made');
    $target = $this->val($n, 'field_target_date');
    if ($made && $target && $target < $made) {
      $this->fail('The target date cannot be before the date the commitment was made (rule V3).');
    }
    $this->notFuture($n, 'field_verified_on', 'The verified-on date');
    // V1 and P1: required before review.
    if (in_array($state, ['in_review', 'published'], TRUE)) {
      $illustrative = (bool) $this->val($n, 'field_is_illustrative');
      $missing = [];
      foreach (['field_date_made' => 'date made', 'field_status' => 'status', 'field_source_name' => 'source name'] as $f => $label) {
        if ($n->get($f)->isEmpty()) {
          $missing[] = $label;
        }
      }
      if (!$illustrative) {
        foreach (['field_source_url' => 'source URL', 'field_verified_on' => 'verified-on date'] as $f => $label) {
          if ($n->get($f)->isEmpty()) {
            $missing[] = $label;
          }
        }
      }
      if ($missing) {
        $this->fail('Before review, add: ' . implode(', ', $missing) . ' (rules V1 and P1).');
      }
    }
    // P1: cannot publish with an unchecked licence.
    if ($state === 'published' && $this->termCode($n, 'field_licence') === 'not-checked') {
      $this->fail('A commitment whose licence is "Not yet checked" cannot be published (rule P1).');
    }
    // V9: rejection needs a comment.
    if ($state === 'rejected' && $n->get('field_review_comment')->isEmpty()) {
      $this->fail('A rejected commitment needs a review comment (rule V9).');
    }
    // V7: cannot be Completed while milestones are open (contributors only).
    if ($this->termCode($n, 'field_status') === 'completed' && !$n->isNew() && $this->isContributor()) {
      $open = $this->entityTypeManager->getStorage('node')->getQuery()->accessCheck(FALSE)
        ->condition('type', 'milestone')->condition('field_commitment', $n->id())->execute();
      foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($open) as $m) {
        if ($this->termCode($m, 'field_milestone_status') !== 'completed') {
          $this->fail('A commitment cannot be Completed while it has milestones that are not Completed (rule V7).');
          break;
        }
      }
    }
    $this->contributorCountry($n, $country_id);
  }

  protected function milestone(NodeInterface $n): void {
    $this->notFuture($n, 'field_completed_on', 'The completed-on date');
    $commitment = $n->get('field_commitment')->entity;
    $done = $this->val($n, 'field_completed_on');
    $made = $commitment ? $commitment->get('field_date_made')->value : NULL;
    if ($done && $made && $done < $made) {
      $this->fail("The completed-on date cannot be before the commitment's date (rule V3).");
    }
    if ($done && $this->termCode($n, 'field_milestone_status') !== 'completed') {
      $this->fail('A milestone with a completed-on date must have the status Completed.');
    }
    $allowed = ['not-started', 'in-progress', 'completed', 'delayed'];
    $code = $this->termCode($n, 'field_milestone_status');
    if ($code && !in_array($code, $allowed, TRUE)) {
      $this->fail('A milestone status must be Not started, In progress, Completed or Delayed.');
    }
    $this->contributorCountry($n, $commitment ? $commitment->get('field_country')->target_id : NULL);
  }

  protected function progressUpdate(NodeInterface $n): void {
    $this->notFuture($n, 'field_update_date', 'The update date');
    $len = mb_strlen(trim(strip_tags((string) $this->val($n, 'field_summary'))));
    if ($len && ($len < 20 || $len > 800)) {
      $this->fail('The summary must be between 20 and 800 characters.');
    }
    if ($this->moderationState($n) === 'rejected' && $n->get('field_review_comment')->isEmpty()) {
      $this->fail('A rejected progress update needs a review comment (rule V9).');
    }
    $commitment = $n->get('field_commitment')->entity;
    $this->contributorCountry($n, $commitment ? $commitment->get('field_country')->target_id : NULL);
  }

  protected function indicator(NodeInterface $n): void {
    $code = (string) $this->val($n, 'field_code');
    if ($code !== '' && $this->exists($n, ['field_code' => $code])) {
      $this->fail("An indicator with code $code already exists (rule V5).");
    }
  }

  protected function indicatorValue(NodeInterface $n): void {
    $year = (int) $this->val($n, 'field_year');
    if ($year && ($year < 1990 || $year > (int) date('Y'))) {
      $this->fail('The year must be between 1990 and the current year.');
    }
    $value = $this->val($n, 'field_value');
    $indicator = $n->get('field_indicator')->entity;
    if ($value !== NULL && $value !== '') {
      if ((float) $value < 0) {
        $this->fail('The value cannot be negative (rule V4).');
      }
      if ($indicator && str_contains((string) $indicator->get('field_unit')->value, '%') && (float) $value > 100) {
        $this->fail('A percentage cannot be above 100 (rule V4).');
      }
    }
    $sex = $this->val($n, 'field_sex');
    if ($indicator && $n->get('field_country')->target_id && $year) {
      $cond = ['field_indicator' => $indicator->id(), 'field_country' => $n->get('field_country')->target_id, 'field_year' => $year];
      if ($sex) {
        $cond['field_sex'] = $sex;
      }
      if ($this->exists($n, $cond)) {
        $this->fail('A value for this indicator, country, year and sex already exists (rule V5).');
      }
    }
    $this->notFuture($n, 'field_retrieved_on', 'The retrieved-on date');
    if (!$this->val($n, 'field_is_illustrative')) {
      foreach (['field_source_url' => 'source URL'] as $f => $label) {
        if ($n->get($f)->isEmpty()) {
          $this->fail("A real value needs a $label (rule P1).");
        }
      }
      if ($this->termCode($n, 'field_licence') === 'not-checked') {
        $this->fail('The licence must be checked before a real value is added (rule P1).');
      }
    }
  }

  protected function document(NodeInterface $n): void {
    if ($n->get('field_file')->isEmpty() && $n->get('field_external_url')->isEmpty()) {
      $this->fail('Add a file or an external URL.');
    }
  }

  protected function isContributor(): bool {
    $roles = $this->currentUser->getRoles();
    return in_array('contributor', $roles, TRUE) && !array_intersect(['editor', 'administrator'], $roles);
  }

  /**
   * Rule V11: contributors work only on their assigned country.
   */
  protected function contributorCountry(NodeInterface $n, mixed $country_id): void {
    if (!$this->isContributor()) {
      return;
    }
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $assigned = $user ? $user->get('field_assigned_country')->target_id : NULL;
    if (!$assigned || ($country_id && $assigned != $country_id)) {
      $this->fail('Contributors can create and edit records only for their assigned country (rule V11).');
    }
  }

}
