<?php

use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Extra steps for the tracker journeys: browser language and response headers.
 */
class FeatureContext extends MinkContext {

  /**
   * @Given my browser prefers :language
   */
  public function myBrowserPrefers(string $language): void {
    $this->getSession()->setRequestHeader('Accept-Language', $language);
  }

  /**
   * @When I request :path without following redirects
   */
  public function iRequestWithoutFollowing(string $path): void {
    $this->getSession()->getDriver()->getClient()->followRedirects(FALSE);
    $this->visitPath($path);
  }

  /**
   * @Then the redirect should point to :path
   */
  public function theRedirectShouldPointTo(string $path): void {
    $location = (string) $this->getSession()->getResponseHeader('Location');
    if (!str_ends_with(parse_url($location, PHP_URL_PATH) ?: $location, $path)) {
      throw new \Exception("Expected a redirect to $path, got '$location'.");
    }
  }

  /**
   * @Then the response header :name should contain :text
   */
  public function theResponseHeaderShouldContain(string $name, string $text): void {
    $value = (string) $this->getSession()->getResponseHeader($name);
    if (stripos($value, $text) === FALSE) {
      throw new \Exception("Header $name is '$value', expected it to contain '$text'.");
    }
  }

  /**
   * @AfterScenario
   */
  public function resetRedirects(): void {
    try {
      $this->getSession()->getDriver()->getClient()->followRedirects(TRUE);
      $this->getSession()->setRequestHeader('Accept-Language', '');
    }
    catch (\Throwable $e) {
    }
  }


  /**
   * @Given there are no test commitments
   * @Then the test commitments are removed
   */
  public function removeTestCommitments(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    foreach (['Behat test commitment', 'Behat test commitment with bad licence'] as $title) {
      foreach ($storage->loadByProperties(['title' => $title]) as $node) {
        $node->delete();
      }
    }
  }

  /**
   * @Given there are no test indicator values
   * @Then the test indicator values are removed
   */
  public function removeTestIndicatorValues(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'indicator_value')
      ->condition('title', 'SH.STA.SUIC.P5 | KEN | 2022 |', 'STARTS_WITH')->execute();
    foreach ($storage->loadMultiple($ids) as $node) {
      $node->delete();
    }
  }

  /**
   * @Then the test indicator value does not exist
   */
  public function theTestIndicatorValueDoesNotExist(): void {
    $ids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()->accessCheck(FALSE)
      ->condition('type', 'indicator_value')->condition('title', 'SH.STA.SUIC.P5 | KEN | 2022 |', 'STARTS_WITH')->execute();
    if ($ids) {
      throw new \Exception('The check-only run created a record.');
    }
  }

  /**
   * @Then the test indicator value exists but is not published
   */
  public function theTestIndicatorValueIsUnpublished(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'indicator_value')
      ->condition('title', 'SH.STA.SUIC.P5 | KEN | 2022 |', 'STARTS_WITH')->execute();
    if (count($ids) !== 1) {
      throw new \Exception('Expected one imported value, found ' . count($ids) . '.');
    }
    if ($storage->load(reset($ids))->isPublished()) {
      throw new \Exception('The imported value is published; it should be held for review.');
    }
  }

  /**
   * Creates an illustrative Kenya commitment and moves it to the given state.
   */
  protected function createTestCommitment(string $title, string $slug, string $final_state): void {
    $etm = \Drupal::entityTypeManager();
    $first = fn(string $type, string $field, string $value) => (int) current($etm->getStorage('node')->getQuery()->accessCheck(FALSE)->condition('type', $type)->condition($field, $value)->execute());
    $term = fn(string $vid, string $name) => (int) key($etm->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid, 'name' => $name]));
    $node = \Drupal\node\Entity\Node::create([
      'type' => 'commitment', 'title' => $title, 'langcode' => 'en', 'status' => 0,
      'field_country' => $first('country', 'field_iso3', 'KEN'),
      'field_institution' => $first('institution', 'field_code', 'KEN-MOH'),
      'field_theme' => [['target_id' => $term('theme', 'Policy and legislation')]],
      'field_commitment_type' => $term('commitment_type', 'Policy'),
      'field_description' => ['value' => 'Illustrative record used only by the automated tests. It does not describe a real commitment by any government or institution.', 'format' => 'basic_html'],
      'field_source_name' => 'Test fixture', 'field_licence' => $term('licence', 'Other, see note'),
      'field_source_url' => ['uri' => 'https://example.org/test'], 'field_verified_on' => '2026-10-29',
      'field_is_illustrative' => 1, 'field_date_made' => '2025-01-01',
      'field_status' => $term('status', 'In progress'), 'field_status_updated_on' => '2026-10-29',
      'path' => ['alias' => '/commitments/' . $slug, 'pathauto' => 0],
      'moderation_state' => 'draft',
    ]);
    $node->save();
    foreach (['in_review', 'published'] as $state) {
      $node->set('moderation_state', $state);
      $node->setNewRevision(TRUE);
      $node->save();
      if ($state === $final_state) {
        break;
      }
    }
  }

  /**
   * @Given an illustrative commitment is waiting in review
   */
  public function anIllustrativeCommitmentIsWaitingInReview(): void {
    $this->createTestCommitment('Behat test commitment', 'behat-test-commitment', 'in_review');
  }

  /**
   * @Given :count illustrative commitments are published
   */
  public function illustrativeCommitmentsArePublished(int $count): void {
    for ($i = 1; $i <= $count; $i++) {
      $n = sprintf('%02d', $i);
      $this->createTestCommitment("Behat paging test $n", "behat-paging-test-$n", 'published');
    }
  }

  /**
   * @Then the paging test commitments are removed
   */
  public function removePagingTestCommitments(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'commitment')->condition('title', 'Behat paging test', 'STARTS_WITH')->execute();
    foreach ($storage->loadMultiple($ids) as $node) {
      $node->delete();
    }
  }

  /**
   * @When I open the edit page of the test commitment
   */
  public function iOpenTheEditPageOfTheTestCommitment(): void {
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['title' => 'Behat test commitment']);
    if (!$nodes) {
      throw new \Exception('The test commitment was not found.');
    }
    $this->visitPath('/node/' . key($nodes) . '/edit');
  }

  /**
   * @Then the :field list should not offer :option
   */
  public function theListShouldNotOffer(string $field, string $option): void {
    $select = $this->getSession()->getPage()->findField($field);
    if (!$select) {
      throw new \Exception("Field $field was not found.");
    }
    foreach ($select->findAll('css', 'option') as $item) {
      if (trim($item->getText()) === $option) {
        throw new \Exception("The $field list offers $option.");
      }
    }
  }

}
