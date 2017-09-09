<?php

namespace Drupal\search_api_algolia\Plugin\search_api\processor\Property;

use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Item\FieldInterface;

class FacetAttributeProperty extends SearchableAttributeProperty {

  public function defaultConfiguration() {
    return [
      'field' => NULL,
      'filter_only' => 0,
      'searchable' => 0,
    ];
  }

  public function buildConfigurationForm(FieldInterface $field, array $form, FormStateInterface $form_state) {
    $configuration = $field->getConfiguration();
    $index = $field->getIndex();
    $form['#tree'] = TRUE;

    $form['#field'] = $field;

    $fields = $this->getFieldOptions($index);

    $form['field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field'),
      '#options' => $fields,
      '#required' => TRUE,
      '#default_value' => $configuration['field'],
    ];

    $form['filter_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Filter only'),
      '#default_value' => $configuration['filter_only'],
    ];

    $form['searchable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Searchable'),
      '#default_value' => $configuration['searchable'],
    ];

    return $form;
  }

  public function validateConfigurationForm(FieldInterface $field, array &$form, FormStateInterface $form_state) {
    // A facet cannot be searchable and filter only at the same time.
    if ($form_state->getValue('filter_only') && $form_state->getValue('searchable')) {
      $form_state->setErrorByName('', $this->t('A facet cannot be searchable and filter only at the same time.'));
    }
    parent::validateConfigurationForm($field, $form, $form_state);
  }

}
