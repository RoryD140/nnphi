<?php

namespace Drupal\search_api_algolia\Plugin\search_api\processor\Property;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\ConfigurablePropertyBase;

class SearchableAttributeProperty extends ConfigurablePropertyBase {

  use StringTranslationTrait;

  public function defaultConfiguration() {
    return [
      'field' => NULL,
      'ranking' => 0,
      'unordered' => 0,
    ];
  }

  public function buildConfigurationForm(FieldInterface $field, array $form, FormStateInterface $form_state) {
    $configuration = $field->getConfiguration();
    $index = $field->getIndex();
    $form['#tree'] = TRUE;

    $form['#field'] = $field;

    $field_options = $this->getFieldOptions($index);

    $form['field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field'),
      '#options' => $field_options,
      '#required' => TRUE,
      '#default_value' => $configuration['field'],
    ];

    $form['ranking'] = [
      '#type' => 'select',
      '#options' => range(0, count($field_options)),
      '#title' => $this->t('Ranking'),
      '#default_value' => $configuration['ranking'],
    ];

    $form['unordered'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Unordered'),
      '#default_value' => $configuration['unordered'],
    ];

    return $form;
  }

  public function validateConfigurationForm(FieldInterface $field, array &$form, FormStateInterface $form_state) {
    $this->validateUniqueField($form_state);
    parent::validateConfigurationForm($field, $form, $form_state);
  }

  protected function validateUniqueField(FormStateInterface $form_state) {
    /** @var FieldInterface $field */
    $field = $form_state->getCompleteForm()['#field'];
    $searchable_field = $form_state->getValue('field');
    // Make sure no other fields exist that use this configuration.
    $index = $field->getIndex();
    $fields = $index->getFields();
    $current_id = $field->getFieldIdentifier();
    $property = $field->getPropertyPath();
    foreach ($fields as $index_field) {
      if ($index_field->getFieldIdentifier() == $current_id) {
        continue;
      }
      if ($index_field->getPropertyPath() == $property) {
        // Check field configuration.
        $config = $index_field->getConfiguration();
        if ($config['field'] === $searchable_field) {
          $form_state->setErrorByName('field', $this->t('This field is already being used for an @processor',
            ['@processor' => $field->getDataDefinition()->getLabel()]));
        }
      }
    }
  }

  protected function getFieldOptions(IndexInterface $index) {
    $field_options = [];
    $fields = $index->getFields();
    foreach ($fields as $field) {
      $type = $field->getDataDefinition()->getDataType();
      if ($type == 'algolia_option') {
        continue;
      }
      $field_options[$field->getFieldIdentifier()] = $field->getLabel();
    }

    return $field_options;
  }

}
