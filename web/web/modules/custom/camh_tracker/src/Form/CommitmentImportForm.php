<?php

namespace Drupal\camh_tracker\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\camh_tracker\Service\CommitmentCsvImporter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin form: upload a commitments CSV, check it, then load valid rows as Drafts.
 */
class CommitmentImportForm extends FormBase {

  public function __construct(protected CommitmentCsvImporter $importer) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('camh_tracker.commitment_csv_importer'));
  }

  public function getFormId(): string {
    return 'camh_tracker_commitment_import';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['intro'] = [
      '#markup' => '<p>' . $this->t('Upload a CSV file that uses the column headings of <code>data/commitments.csv</code>. Choose <em>Check only</em> to see a report without changing anything, then <em>Check and import</em> to load the valid rows. Every imported commitment is created as a Draft and goes through editorial review before it can be published. Rows that match an existing commitment (same title and country) are skipped.') . '</p>',
    ];
    $form['file'] = [
      '#type' => 'file',
      '#title' => $this->t('CSV file'),
      '#description' => $this->t('Plain CSV, UTF-8, up to 2 MB.'),
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['check'] = [
      '#type' => 'submit', '#value' => $this->t('Check only'), '#name' => 'check',
    ];
    $form['actions']['import'] = [
      '#type' => 'submit', '#value' => $this->t('Check and import'), '#name' => 'import',
      '#button_type' => 'primary',
    ];
    if ($report = $form_state->get('report')) {
      $form['report'] = $report;
    }
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $files = $this->getRequest()->files->get('files', []);
    $file = $files['file'] ?? NULL;
    if (!$file || !$file->isValid()) {
      $form_state->setErrorByName('file', $this->t('Choose a CSV file to upload.'));
      return;
    }
    if (strtolower($file->getClientOriginalExtension()) !== 'csv') {
      $form_state->setErrorByName('file', $this->t('The file must have the extension .csv.'));
      return;
    }
    if ($file->getSize() > 2 * 1024 * 1024) {
      $form_state->setErrorByName('file', $this->t('The file is larger than 2 MB.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $file = $this->getRequest()->files->get('files')['file'];
    $save = $form_state->getTriggeringElement()['#name'] === 'import';
    $result = $this->importer->process($file->getPathname(), $save);
    if ($result['fatal']) {
      $this->messenger()->addError($result['fatal']);
      return;
    }
    $counts = ['ok' => 0, 'loaded' => 0, 'skipped' => 0, 'error' => 0];
    $rows = [];
    foreach ($result['rows'] as [$line, $title, $state, $message]) {
      $counts[$state]++;
      $label = ['ok' => $this->t('Valid'), 'loaded' => $this->t('Loaded'), 'skipped' => $this->t('Skipped'), 'error' => $this->t('Error')][$state];
      $rows[] = [$line, $title, $label, $message];
    }
    $summary = $save
      ? $this->t('Import finished: @n loaded as Drafts, @s skipped, @e with errors (not loaded).', ['@n' => $counts['loaded'], '@s' => $counts['skipped'], '@e' => $counts['error']])
      : $this->t('Check finished, nothing was changed: @n valid, @s would be skipped, @e with errors.', ['@n' => $counts['ok'], '@s' => $counts['skipped'], '@e' => $counts['error']]);
    $counts['error'] ? $this->messenger()->addWarning($summary) : $this->messenger()->addStatus($summary);
    $form_state->set('report', [
      '#type' => 'table',
      '#caption' => $this->t('Import report'),
      '#header' => [$this->t('Row'), $this->t('Title'), $this->t('Result'), $this->t('Details')],
      '#rows' => $rows,
      '#empty' => $this->t('The file has no data rows.'),
    ]);
    $form_state->setRebuild();
  }

}
